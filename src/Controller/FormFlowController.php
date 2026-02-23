<?php

namespace App\Controller;

use App\Form\Data\BasicDto;
use App\Form\Type\BasicType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Flow\FormFlowInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FormFlowController extends AbstractController
{
    #[Route('/formflow', name: 'form_flow_demo')]
    public function __invoke(Request $request): Response
    {
        /** @var FormFlowInterface $flow */
        $flow = $this->createForm(BasicType::class, new BasicDto())
            ->handleRequest($request);

        if ($flow->isSubmitted() && $flow->isValid() && $flow->isFinished()) {

            $this->addFlash('success', 'Your form flow was successfully finished!');

            return $this->redirectToRoute('form_flow_demo');
        }

        return $this->render('form_flow/basic.html.twig', [
            'form' => $flow->getStepForm(),
        ]);
    }
}
