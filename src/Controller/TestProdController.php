<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/test/prod')]
final class TestProdController extends AbstractController
{
    #[Route(name: 'app_test_prod_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {

        
        $products = $entityManager
            ->getRepository(Product::class)
            ->findAll();

        return $this->render('test_prod/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/new', name: 'app_test_prod_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductForm::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_test_prod_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('test_prod/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_test_prod_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('test_prod/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_test_prod_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductForm::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_test_prod_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('test_prod/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_test_prod_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_test_prod_index', [], Response::HTTP_SEE_OTHER);
    }
}
