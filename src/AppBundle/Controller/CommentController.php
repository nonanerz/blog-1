<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Author;
use AppBundle\Entity\Comment;
use AppBundle\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CommentController.
 */
class CommentController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAllAction()
    {
        $em = $this->getDoctrine()->getManager();

        $comments = $em->getRepository('AppBundle:Comment')
            ->findAll();

        return $this->render('Comment/comment.html.twig', [
            'comments' => $comments,
        ]);
    }

    /**
     * @param Request $request
     * @param int     $page
     * @Route("/admin/comments/{page}", name="check_comments")
     *
     * @return Response
     */
    public function checkCommentsAction(Request $request, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $pagination = $this->pagination($em->getRepository('AppBundle:Comment')
            ->findAllOrdered(), $request->query->getInt('page', $page), 10);

        return $this->render('Admin/comments.html.twig', [
            'comments' => $pagination,
        ]);
    }

    /**
     * @param Article $article
     * @param Comment $comment
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/remove_comment/{article}/{comment}", name="remove_comment")
     */
    public function removeAction(Article $article, Comment $comment)
    {
        $article->getComments()->removeElement($comment);
        $comment->setArticle(null);
        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->persist($comment);
        $em->flush();

        return $this->redirectToRoute('show_article', ['id' => $article->getId()]);
    }

    /**
     * @param Request $request
     * @param Author  $author
     * @param int     $page
     * @Route("/admin/comments/authoredComments/authorId={author}/{page}", name="authored_comments")
     *
     * @return Response
     */
    public function showByAuthor(Request $request, Author $author, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $comments = $em->getRepository('AppBundle:Comment')
            ->findByAuthor($author);

        if (!$comments) {
            throw new NotFoundHttpException();
        }

        $page = $request->query->getInt('page', $page);

        $pagination = $this->pagination($comments, $page, 10);

        return $this->render('Admin/comments.html.twig', [
            'comments' => $pagination,
        ]);
    }

    /**
     * @param $query
     * @param $currentPage
     * @param $perPage
     *
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    public function pagination($query, $currentPage, $perPage)
    {
        $paginator = $this->get('knp_paginator');

        return $paginator->paginate($query, $currentPage, $perPage);
    }

    /**
     * @param Request $request
     * @param Article $article
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route("/articles/{id}/newComment", name="new_comment")
     */
    public function newAction(Request $request, Article $article = null)
    {
        $form = $this->createForm(CommentType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $comment = $form->getData();

            $author = $em->getRepository('AppBundle:Author')
                ->find(15);

            $comment->setAuthor($author);

            $comment->setArticle($article);

            $article->addComment($comment);

            $em->persist($article);
            $em->persist($comment);

            $em->flush();

            $this->addFlash('success', 'New comment created');

            return $this->redirectToRoute('show_article', ['id' => $article->getId()]);
        }

        return $this->render(':Forms:newComment.html.twig', [
            'commentType' => $form->createView(),
            'id' => $article->getId(),
        ]);
    }
}
