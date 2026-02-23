<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/clients')]
class ClientController extends AbstractController
{
    #[Route('', name: 'client_index', methods: ['GET'])]
    #[IsGranted('CLIENT_VIEW')]
    public function index(ClientRepository $clientRepository): Response
    {
        $clients = $clientRepository->findAllSortedByLastName();

        return $this->render('client/index.html.twig', ['clients' => $clients]);
    }

    #[Route('/new', name: 'client_new', methods: ['GET', 'POST'])]
    #[IsGranted('CLIENT_CREATE')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($client);
            $em->flush();
            $this->addFlash('success', 'Client ajouté avec succès');

            return $this->redirectToRoute('client_index');
        }

        return $this->render('client/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'client_edit', methods: ['GET', 'POST'])]
    #[IsGranted('CLIENT_EDIT', subject: 'client')]
    public function edit(Client $client, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Client mis à jour avec succès');

            return $this->redirectToRoute('client_index');
        }

        return $this->render('client/edit.html.twig', [
            'form' => $form,
            'client' => $client,
        ]);
    }

    #[Route('/{id}/delete', name: 'client_delete', methods: ['POST'])]
    #[IsGranted('CLIENT_DELETE', subject: 'client')]
    public function delete(Client $client, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $client->getId(), $request->request->get('_token'))) {
            $em->remove($client);
            $em->flush();
            $this->addFlash('success', 'Client supprimé avec succès');
        }

        return $this->redirectToRoute('client_index');
    }
}
