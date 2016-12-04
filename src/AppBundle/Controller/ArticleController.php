<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Form\ArticleForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ArticleController.
 */
class ArticleController extends Controller
{
    /**
     * @param $request
     * @Route("/article/new", name="new_article")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(ArticleForm::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($form->getData());

            $em->flush();

            $this->addFlash('success', 'New article was created!');

            return $this->redirectToRoute('homepage');
        }

        return $this->render(':Article:new.html.twig', [
            'articleForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/", name="homepage")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();

        $articles = $em->getRepository('AppBundle:Article')
                        ->findAll();

        return $this->render('Article/list.html.twig', [
        'articles' => $articles,
    ]);
    }

    /**
     * @param $request
     * @param $article
     * @Route("/article/{id}/edit", name="edit_article")
     *
     * @return Response
     */
    public function editAction(Request $request, Article $article)
    {
        $form = $this->createForm(ArticleForm::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($form->getData());

            $em->flush();

            $this->addFlash('success', 'Changes saved!');

            return $this->redirectToRoute('homepage');
        }

        return $this->render(':Article:edit.html.twig', [
            'articleForm' => $form->createView(),
        ]);
    }

    /**
     * @param $article
     * @Route("article/{id}/remove", name="remove_article")
     *
     * @return Response
     */
    public function removeAction(Article $article)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }

    /**
     * @param $article|null
     * @Route("/article/{id}", name="show_article")
     *
     * @return Response
     */
    public function showAction(Article $article = null)
    {
        if (!$article) {
            throw $this->createNotFoundException('Article is not exist!');
        }

        $em = $this->getDoctrine()->getManager();

        $articles = $em->getRepository('AppBundle:Article')
            ->findOneBy(['id' => $article]);

        return $this->render('Article/article.html.twig', [
            'article' => $articles,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function topArticlesAction()
    {
        $em = $this->getDoctrine()->getManager();

        $articles = $em->getRepository('AppBundle:Article')
           ->findLastFive();

        return $this->render(':Article:top.html.twig', [
            'articles' => $articles,
        ]);
    }
}
