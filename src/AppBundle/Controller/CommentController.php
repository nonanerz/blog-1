<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Author;
use AppBundle\Entity\Comment;
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
     * @Route("/admin/comments/{page}", name="check_comments")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkCommentsAction(Request $request, $page = 1)
    {
        $page = $request->query->getInt('page', $page);

        $em = $this->getDoctrine()->getManager();

        $pagination = $this->pagination($em->getRepository('AppBundle:Comment')
            ->findAll(), $page, 10);

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
     * @Route("/article/{id}/newComment", name="new_comment")
     *
     * @param Request $request
     * @param Article $article
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(Request $request, Article $article)
    {
        $newComment = new Comment();

        $newComment->setAuthor($this->getUser());

        $newComment->setContent('fdf');

        $newComment->setArticle($article);
        $article->addComment($newComment);

        $em = $this->getDoctrine()->getManager();

        $em->persist($article);
        $em->persist($newComment);
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
    private function pagination($query, $currentPage, $perPage)
    {
        $paginator = $this->get('knp_paginator');

        return $paginator->paginate($query, $currentPage, $perPage);
    }
}
