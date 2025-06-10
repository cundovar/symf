<?php

namespace App\Controller;

use App\Entity\Apropos;
use App\Form\AproposForm;
use App\Repository\AproposRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/apropos')]
final class AdminAproposController extends AbstractController
{
    #[Route(name: 'app_admin_apropos_index', methods: ['GET'])]
    public function index(AproposRepository $aproposRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Accès refusé. Vous devez être administrateur.');
            return $this->redirectToRoute('home'); 
        }
        return $this->render('admin_apropos/index.html.twig', [
            'apropos' => $aproposRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_apropos_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Accès refusé. Vous devez être administrateur.');
            return $this->redirectToRoute('home'); 
        }
        $apropo = new Apropos();
        $form = $this->createForm(AproposForm::class, $apropo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($apropo);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_apropos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_apropos/new.html.twig', [
            'apropo' => $apropo,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_apropos_show', methods: ['GET'])]
    public function show(Apropos $apropo): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Accès refusé. Vous devez être administrateur.');
            return $this->redirectToRoute('home'); 
        }
        return $this->render('admin_apropos/show.html.twig', [
            'apropo' => $apropo,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_apropos_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Apropos $apropo, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Accès refusé. Vous devez être administrateur.');
            return $this->redirectToRoute('home'); 
        }
        $form = $this->createForm(AproposForm::class, $apropo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_apropos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_apropos/edit.html.twig', [
            'apropo' => $apropo,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_apropos_delete', methods: ['POST'])]
    public function delete(Request $request, Apropos $apropo, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Accès refusé. Vous devez être administrateur.');
            return $this->redirectToRoute('home'); 
        }
        if ($this->isCsrfTokenValid('delete'.$apropo->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($apropo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_apropos_index', [], Response::HTTP_SEE_OTHER);
    }
}
