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
     * @param Request $request
     * @param int     $page
     * @Route("/admin/comments/{page}", name="check_comments")
     *
     * @return Response
     */
    public function checkCommentsAction(Request $request, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $pagination = $this->get('knp_paginator')
            ->paginate($em->getRepository('AppBundle:Comment')
                ->findAllOrdered(), $request->query->getInt('page', $page), 10);

        return $this->render('Admin/comments.html.twig', [
            'comments' => $pagination,
        ]);
    }

    /**
     * @param Request $request
     * @param Comment $comment
     * @param Article $article
     * @Route("/remove_comment/{article}/{comment}", name="remove_comment")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function deleteAction(Request $request, Comment $comment, Article $article)
    {

        $form = $this->get('app.form_manager')
            ->removeCommentForm($request, $comment, $article);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();

            return $this->redirectToRoute('show_article', ['id' => $article->getId()]);
        }

        return $this->render(':Forms:deleteComment.html.twig', [
            'deleteCommentForm' => $form->createView(),
        ]);
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

        $pagination = $this->get('knp_paginator')
            ->paginate($comments, $page, 10);

        return $this->render('Admin/comments.html.twig', [
            'comments' => $pagination,
        ]);
    }


    /**
     * @param Comment $comment
     * @Route("/admin/comments/status/{id}", name="comment_allowed")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function allowedAction(Comment $comment)
    {
        $em = $this->getDoctrine()->getManager();

        if ($comment->getIsPublished()){

            $comment->setIsPublished(false);
        }else{
            $comment->setIsPublished(true);
        }

        $em->persist($comment);

        $em->flush();

        return $this->redirectToRoute('check_comments');
    }

    /**
     *
     * @return Response
     */
    public function countUnpublishedAction()
    {
        $em = $this->getDoctrine()->getManager();

        $result = $em->getRepository('AppBundle:Comment')
            ->countUnpublished();

        return new Response($result[1]);
    }
}
