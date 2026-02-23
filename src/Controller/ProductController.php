<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\Product\ProductDetailsStepType;
use App\Form\Product\ProductLicenseStepType;
use App\Form\Product\ProductLogisticsStepType;
use App\Form\Product\ProductPriceConfirmationStepType;
use App\Form\Product\ProductTypeStepType;
use App\Repository\ProductRepository;
use App\Service\ProductCsvExporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/products')]
class ProductController extends AbstractController
{
    // Price threshold for confirmation step
    private const PRICE_THRESHOLD = 1000;

    #[Route('', name: 'product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAllSortedByPriceDesc();

        return $this->render('product/index.html.twig', ['products' => $products]);
    }

    /**
     * Calculate dynamic steps based on flow data
     */
    private function getDynamicSteps(array $flow): array
    {
        $steps = [1, 2]; // type, details always present
        
        $type = $flow['type']['product_type'] ?? 'physical';
        $price = $flow['details']['price'] ?? 0;
        
        if ($type === 'physical') {
            $steps[] = 3; // logistics
        } else {
            $steps[] = 4; // license
        }
        
        // Add price confirmation if price >= threshold
        if ($price >= self::PRICE_THRESHOLD) {
            $nextStep = max($steps) + 1;
            $steps[] = $nextStep;
        }
        
        $steps[] = 99; // summary (always last)
        return $steps;
    }

    #[Route('/new', name: 'product_new', methods: ['GET','POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $flow = $session->get('product_flow', []);
        $step = $request->query->getInt('step', 1);
        $direction = $request->query->get('direction', 'next'); // 'next' or 'prev'

        // Step 1: Product Type
        if ($step === 1) {
            $form = $this->createForm(ProductTypeStepType::class, $flow['type'] ?? null);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $flow['type'] = $form->getData();
                $session->set('product_flow', $flow);
                return $this->redirectToRoute('product_new', ['step' => 2]);
            }

            $steps = [1, 2, 3, 4, 99];
            return $this->render('product/new_step.html.twig', [
                'form' => $form->createView(),
                'step' => 1,
                'steps' => $steps,
                'title' => 'Type de Produit',
                'showPrevious' => false,
            ]);
        }

        // Step 2: Product Details
        if ($step === 2) {
            $form = $this->createForm(ProductDetailsStepType::class, $flow['details'] ?? null);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $flow['details'] = $form->getData();
                $session->set('product_flow', $flow);
                
                // Calculate next step dynamically
                $nextStep = 3;
                $type = $flow['type']['product_type'] ?? 'physical';
                if ($type === 'digital') {
                    $nextStep = 4; // skip logistics, go to license
                }
                
                return $this->redirectToRoute('product_new', ['step' => $nextStep]);
            }

            $steps = [1, 2, 3, 4, 99];
            return $this->render('product/new_step.html.twig', [
                'form' => $form->createView(),
                'step' => 2,
                'steps' => $steps,
                'title' => 'Détails du Produit',
                'showPrevious' => true,
            ]);
        }

        // Step 3: Logistics (Physical products only)
        if ($step === 3) {
            $form = $this->createForm(ProductLogisticsStepType::class, $flow['logistics'] ?? null);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $flow['logistics'] = $form->getData();
                $session->set('product_flow', $flow);
                
                // Check if price confirmation needed
                $price = $flow['details']['price'] ?? 0;
                $nextStep = ($price >= self::PRICE_THRESHOLD) ? 5 : 99;
                
                return $this->redirectToRoute('product_new', ['step' => $nextStep]);
            }

            $steps = [1, 2, 3, 99];
            return $this->render('product/new_step.html.twig', [
                'form' => $form->createView(),
                'step' => 3,
                'steps' => $steps,
                'title' => 'Logistique du Produit',
                'showPrevious' => true,
            ]);
        }

        // Step 4: License (Digital products only)
        if ($step === 4) {
            $form = $this->createForm(ProductLicenseStepType::class, $flow['license'] ?? null);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $flow['license'] = $form->getData();
                $session->set('product_flow', $flow);
                
                // Check if price confirmation needed
                $price = $flow['details']['price'] ?? 0;
                $nextStep = ($price >= self::PRICE_THRESHOLD) ? 5 : 99;
                
                return $this->redirectToRoute('product_new', ['step' => $nextStep]);
            }

            $steps = [1, 2, 4, 99];
            return $this->render('product/new_step.html.twig', [
                'form' => $form->createView(),
                'step' => 4,
                'steps' => $steps,
                'title' => 'Licence du Produit',
                'showPrevious' => true,
            ]);
        }

        // Step 5: Price Confirmation (only if price >= PRICE_THRESHOLD)
        if ($step === 5) {
            $form = $this->createForm(ProductPriceConfirmationStepType::class, $flow['confirmation'] ?? null);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $flow['confirmation'] = $form->getData();
                $session->set('product_flow', $flow);
                return $this->redirectToRoute('product_new', ['step' => 99]);
            }

            $type = $flow['type']['product_type'] ?? 'physical';
            $prevStep = ($type === 'physical') ? 3 : 4;
            $steps = [1, 2, ($type === 'physical' ? 3 : 4), 5, 99];
            
            return $this->render('product/new_step.html.twig', [
                'form' => $form->createView(),
                'step' => 5,
                'steps' => $steps,
                'title' => 'Confirmation du Prix',
                'showPrevious' => true,
                'prevStep' => $prevStep,
            ]);
        }

        // Step 99: Summary
        if ($step === 99) {
            $form = $this->createFormBuilder()
                ->setMethod('POST')
                ->getForm();
            
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // Persist product
                $details = $flow['details'] ?? [];
                $product = new Product();
                $product->setName($details['name'] ?? '');
                $product->setDescription($details['description'] ?? '');
                $product->setPrice($details['price'] ?? 0);
                $product->setProductType($flow['type']['product_type'] ?? 'physical');
                $em->persist($product);
                $em->flush();
                $session->remove('product_flow');
                $this->addFlash('success', 'Produit ajouté avec succès');
                return $this->redirectToRoute('product_index');
            }

            $type = $flow['type']['product_type'] ?? 'physical';
            $price = $flow['details']['price'] ?? 0;
            $prevStep = ($price >= self::PRICE_THRESHOLD) ? 5 : (($type === 'physical') ? 3 : 4);
            
            return $this->render('product/summary.html.twig', [
                'form' => $form->createView(),
                'flow' => $flow,
                'step' => 99,
                'prevStep' => $prevStep,
            ]);
        }

        return new RedirectResponse($this->generateUrl('product_new', ['step' => 1]));
    }

    #[Route('/{id}/edit', name: 'product_edit', methods: ['GET','POST'])]
    #[IsGranted('PRODUCT_EDIT', subject: 'product')]
    public function edit(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        
        // Initialize flow with existing product data
        if (!$session->has('product_flow_edit_' . $product->getId())) {
            $session->set('product_flow_edit_' . $product->getId(), [
                'type' => ['product_type' => $product->getProductType() ?? 'physical'],
                'details' => [
                    'name' => $product->getName(),
                    'description' => $product->getDescription(),
                    'price' => (string)$product->getPrice(),
                ],
                'logistics' => [],
                'license' => [],
            ]);
        }
        
        $flow = $session->get('product_flow_edit_' . $product->getId(), []);
        $step = $request->query->getInt('step', 1);
        $direction = $request->query->get('direction', 'next');

        // Step 1: Product Type
        if ($step === 1) {
            $form = $this->createForm(ProductTypeStepType::class, $flow['type'] ?? null);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $flow['type'] = $form->getData();
                $session->set('product_flow_edit_' . $product->getId(), $flow);
                return $this->redirectToRoute('product_edit', ['id' => $product->getId(), 'step' => 2]);
            }

            $steps = [1, 2, 3, 4, 99];
            return $this->render('product/new_step.html.twig', [
                'form' => $form->createView(),
                'step' => 1,
                'steps' => $steps,
                'title' => 'Type de Produit',
                'showPrevious' => false,
                'product_id' => $product->getId(),
            ]);
        }

        // Step 2: Product Details
        if ($step === 2) {
            $form = $this->createForm(ProductDetailsStepType::class, $flow['details'] ?? null);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $flow['details'] = $form->getData();
                $session->set('product_flow_edit_' . $product->getId(), $flow);
                
                // Calculate next step dynamically
                $nextStep = $flow['type']['product_type'] === 'physical' ? 3 : 4;
                return $this->redirectToRoute('product_edit', ['id' => $product->getId(), 'step' => $nextStep]);
            }

            $steps = $this->getDynamicSteps($flow);
            
            return $this->render('product/new_step.html.twig', [
                'form' => $form->createView(),
                'step' => 2,
                'steps' => $steps,
                'title' => 'Détails du Produit',
                'showPrevious' => true,
                'prevStep' => 1,
                'product_id' => $product->getId(),
            ]);
        }

        // Step 3: Logistics (physical only)
        if ($step === 3 && $flow['type']['product_type'] === 'physical') {
            $form = $this->createForm(ProductLogisticsStepType::class, $flow['logistics'] ?? null);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $flow['logistics'] = $form->getData();
                $session->set('product_flow_edit_' . $product->getId(), $flow);
                
                $price = $flow['details']['price'] ?? 0;
                $nextStep = $price >= self::PRICE_THRESHOLD ? 5 : 99;
                return $this->redirectToRoute('product_edit', ['id' => $product->getId(), 'step' => $nextStep]);
            }

            $steps = $this->getDynamicSteps($flow);
            return $this->render('product/new_step.html.twig', [
                'form' => $form->createView(),
                'step' => 3,
                'steps' => $steps,
                'title' => 'Gestion des Stocks & Logistique',
                'showPrevious' => true,
                'prevStep' => 2,
                'product_id' => $product->getId(),
            ]);
        }

        // Step 4: License (digital only)
        if ($step === 4 && $flow['type']['product_type'] === 'digital') {
            $form = $this->createForm(ProductLicenseStepType::class, $flow['license'] ?? null);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $flow['license'] = $form->getData();
                $session->set('product_flow_edit_' . $product->getId(), $flow);
                
                $price = $flow['details']['price'] ?? 0;
                $nextStep = $price >= self::PRICE_THRESHOLD ? 5 : 99;
                return $this->redirectToRoute('product_edit', ['id' => $product->getId(), 'step' => $nextStep]);
            }

            $steps = $this->getDynamicSteps($flow);
            return $this->render('product/new_step.html.twig', [
                'form' => $form->createView(),
                'step' => 4,
                'steps' => $steps,
                'title' => 'Licence Numérique',
                'showPrevious' => true,
                'prevStep' => 2,
                'product_id' => $product->getId(),
            ]);
        }

        // Step 5: Price Confirmation (if price >= threshold)
        if ($step === 5) {
            $price = $flow['details']['price'] ?? 0;
            if ($price >= self::PRICE_THRESHOLD) {
                $form = $this->createForm(ProductPriceConfirmationStepType::class);
                $form->handleRequest($request);
                
                if ($form->isSubmitted() && $form->isValid()) {
                    return $this->redirectToRoute('product_edit', ['id' => $product->getId(), 'step' => 99]);
                }

                $steps = $this->getDynamicSteps($flow);
                $type = $flow['type']['product_type'] ?? 'physical';
                $prevStep = ($type === 'physical') ? 3 : 4;
                
                return $this->render('product/new_step.html.twig', [
                    'form' => $form->createView(),
                    'step' => 5,
                    'steps' => $steps,
                    'title' => 'Confirmation du Prix',
                    'showPrevious' => true,
                    'prevStep' => $prevStep,
                    'product_id' => $product->getId(),
                ]);
            }

            return $this->redirectToRoute('product_edit', ['id' => $product->getId(), 'step' => 99]);
        }

        // Step 99: Summary / Confirmation
        if ($step === 99) {
            $form = $this->createFormBuilder()
                ->setMethod('POST')
                ->getForm();
            
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                // Update product with flow data
                $details = $flow['details'] ?? [];
                $product->setName($details['name'] ?? $product->getName());
                $product->setDescription($details['description'] ?? $product->getDescription());
                $product->setPrice($details['price'] ?? $product->getPrice());
                $product->setProductType($flow['type']['product_type'] ?? $product->getProductType());
                $em->flush();
                $session->remove('product_flow_edit_' . $product->getId());
                $this->addFlash('success', 'Produit mis à jour avec succès');
                return $this->redirectToRoute('product_index');
            }

            $type = $flow['type']['product_type'] ?? 'physical';
            $price = $flow['details']['price'] ?? 0;
            $prevStep = ($price >= self::PRICE_THRESHOLD) ? 5 : (($type === 'physical') ? 3 : 4);
            
            return $this->render('product/summary.html.twig', [
                'form' => $form->createView(),
                'flow' => $flow,
                'step' => 99,
                'prevStep' => $prevStep,
                'product_id' => $product->getId(),
            ]);
        }

        return new RedirectResponse($this->generateUrl('product_edit', ['id' => $product->getId(), 'step' => 1]));
    }

    #[Route('/{id}/delete', name: 'product_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', 'Produit supprimé');
        }

        return $this->redirectToRoute('product_index');
    }

    #[Route('/export/csv', name: 'product_export_csv', methods: ['GET'])]
    public function exportCsv(ProductRepository $repo, ProductCsvExporter $exporter): StreamedResponse
    {
        $products = $repo->findAllSortedByPriceDesc();
        return $exporter->streamProductsCsvResponse($products);
    }
}
