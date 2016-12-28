<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController.
 */
class ArticleController extends Controller
{
    /**
     * @param $request
     * @Route("/article/new", name="new_article")
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(ArticleType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $article = $form->getData();

            if (!$article->getImage()) {
                $article->setImage('50708_1280x720-318x180.jpg');
            }

            $article->setAuthor($em->getRepository('AppBundle:Author')
                ->find(16));

            $em->persist($article);

            $em->flush();

            $this->addFlash('success', 'New article was created!');

            return $this->redirectToRoute('homepage');
        }

        return $this->render(':Article:new.html.twig', [
            'articleType' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{page}", name="homepage", requirements={"page": "\d+"})
     *
     * @param $request
     * @param $page
     *
     * @return Response
     */
    public function listAction(Request $request, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $articles = $em->getRepository('AppBundle:Article')
            ->findAllOrdered();

        if (!$articles) {
            throw new NotFoundHttpException('Noooo!');
        }

        $pagination = $this->pagination($articles,
            $request->query->getInt('page', $page), 5);

        return $this->render('Article/list.html.twig', [
        'articles' => $pagination,
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
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($form->getData());

            $em->flush();

            $this->addFlash('success', 'Changes saved!');

            return $this->redirectToRoute('show_article', ['id' => $article->getId()]);
        }

        return $this->render(':Article:edit.html.twig', [
            'articleType' => $form->createView(),
        ]);
    }

    /**
     * @param $article
     * @Route("article/{id}/remove", name="remove_article", requirements={"id": "\d+"})
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
     * @param Request      $request
     * @param Article|null $article
     * @param int          $page
     * @Route("/article/{id}/{page}", name="show_article", requirements={"id": "\d+", "page": "\d+"})
     *
     * @return Response
     */
    public function showAction(Request $request, Article $article = null, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $article = $em->getRepository('AppBundle:Article')
            ->findByIdOrderedWithJoins($article);

        $comments = $article->getComments();

        if (!$article) {
            throw new NotFoundHttpException('Article is not exist!');
        }

        $pagination = $this->pagination($comments, $request->query->getInt('page', $page), 5);

        return $this->render('Article/article.html.twig', [
            'article' => $article,
            'comments' => $pagination,
        ]);
    }

    /**
     * @return Response
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

    /**
     * @Route("/search", name="search")
     *
     * @param Request $request
     * @param int     $page
     *
     * @return Response
     */
    public function searchAction(Request $request, $page = 1)
    {
        $result = $this->getDoctrine()->getManager()->getRepository('AppBundle:Article')
            ->search($request->query->get('q'));
        if (!$result) {
            $this->addFlash('failure', 'Nothing found');

            return $this->listAction($request);
        } else {
            $pagination = $this->pagination($result, $request->query->getInt('page', $page), 5);
        }

        return $this->render('Article/list.html.twig', [
            'articles' => $pagination,
        ]);
    }

    /**
     * @Route("/article/tag/{tag}/{page}", name="tags", requirements={"page": "\d+"})
     *
     * @param Request $request
     * @param $tag
     * @param int $page
     *
     * @return Response
     */
    public function tagAction(Request $request, $tag, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();
        $tag = $em->getRepository('AppBundle:Tag')
            ->findByTag($tag);

        if (!$tag) {
            throw new NotFoundHttpException();
        }

        $pagination = $this->pagination($tag->getArticles(), $request->query->getInt('page', $page), 5);

        return $this->render('Article/list.html.twig', [
            'articles' => $pagination,
        ]);
    }

    /**
     * @param $query
     * @param $currentPage
     * @param $perPage
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    private function pagination($query, $currentPage, $perPage)
    {
        $paginator = $this->get('knp_paginator');

        return $paginator->paginate($query, $currentPage, $perPage);
    }
}
