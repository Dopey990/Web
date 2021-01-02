<?php

namespace App\Controller;

use DateTime;
use App\Service\FileUploader;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="article_index", methods={"GET"})
     */
    public function index(ArticleRepository $articleRepository): Response
    {
		$this->denyAccessUnlessGranted('ROLE_USER', null, 'Veuillez vous identifier pour continuer.');
		
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="article_new", methods={"GET","POST"})
     */
    public function new(Request $request, FileUploader $fileUploader): Response
    {
		$this->denyAccessUnlessGranted('ROLE_USER', null, 'Veuillez vous identifier pour continuer.');
		
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
			$article->setDateCreation(new DateTime('now')); 
			
		$imageFile = $form->get('Image')->getData();
        if ($imageFile) {
            $imageFilename = $fileUploader->upload($imageFile);
            $article->setImageFilename($imageFilename);
        }
			
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_show", methods={"GET"})
     */
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="article_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Article $article, FileUploader $fileUploader): Response
    {
		$this->denyAccessUnlessGranted('ROLE_USER', null, 'Veuillez vous identifier pour continuer.');
		
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {		
			$imageFile = $form->get('Image')->getData();
			if ($imageFile) {
				$imageFilename = $fileUploader->upload($imageFile);
				$article->setImageFilename($imageFilename);
			}
			else {
				$article->setImageFilename($article->getImageFilename());
			}
		
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Article $article): Response
    {
		$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Veuillez vous identifier avec un compte admin pour continuer.');
		
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_index');
    }
}
