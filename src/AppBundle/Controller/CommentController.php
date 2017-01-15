<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Author;
use AppBundle\Entity\Comment;
use AppBundle\Form\CommentType;
use AppBundle\Security\CommentVoter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CommentController.
 */
class CommentController extends BaseController
{
    /**
     * @param Request $request
     * @param Article $article
     * @Route("/article/comment/new/{article}", name="new_comment")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function newAction(Request $request, Article $article)
    {
        /** @var Form $form */
        $form = $this->createForm(CommentType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();

            $this->denyAccessUnlessGranted(CommentVoter::CREATE_COMMENT, $comment);

            $comment->setAuthor($this->getUser()->getAuthor());

            $comment->setArticle($article);

            $article->addComment($comment);

            $this->em()->persist($article);
            $this->em()->persist($comment);

            $this->em()->flush();

            $this->get('app.notifier')
                ->newCommentNotify($comment, $comment->getAuthor(), $article->getAuthor());

            return $this->redirectToRoute('show_article', ['id' => $article->getId()]);
        }

        return $this->render(':Forms:newComment.html.twig', [
            'newCommentForm' => $form->createView(),
            'article' => $article->getId(),
        ]);
    }

    /**
     * @param Request $request
     * @param int     $page
     * @Route("/admin/comment/{page}", name="check_comments")
     *
     * @return Response
     */
    public function checkCommentsAction(Request $request, $page = 1)
    {
        $pagination = $this->pagination($this->em()->getRepository('AppBundle:Comment')
                ->findAllOrdered(), $request->query->getInt('page', $page), 10);

        return $this->render('Admin/comments.html.twig', [
            'comments' => $pagination,
        ]);
    }

    /**
     * @param Request $request
     * @param Comment $comment
     * @param Article $article
     * @Route("/article/comment/remove/{article}/{comment}", name="remove_comment")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function deleteAction(Request $request, Comment $comment, Article $article)
    {

        /** @var Form $form */
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('remove_comment', [
                'comment' => $comment->getId(),
                'article' => $article->getId(),
            ]))
            ->setMethod('DELETE')
            ->getForm();

        if ($request->isMethod('DELETE')) {
            $this->denyAccessUnlessGranted(CommentVoter::DELETE_COMMENT, $comment);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->em()->remove($comment);
                $this->em()->flush();

                return $this->redirectToRoute('show_article', ['id' => $article->getId()]);
            }
        }

        return $this->render(':Forms:deleteComment.html.twig', [
            'deleteCommentForm' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Author  $author
     * @param int     $page
     * @Route("/admin/comment/authoredComments/authorId={author}/{page}", name="authored_comments")
     *
     * @return Response
     */
    public function showByAuthor(Request $request, Author $author, $page = 1)
    {
        $comments = $this->em()->getRepository('AppBundle:Comment')
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
     * @Route("/admin/comment/status/{id}", name="comment_allowed")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function allowedAction(Comment $comment)
    {
        if ($comment->getIsPublished()) {
            $comment->setIsPublished(false);
        } else {
            $comment->setIsPublished(true);
        }

        $this->em()->persist($comment);

        $this->em()->flush();

        return $this->redirectToRoute('check_comments');
    }

    /**
     * @return Response
     */
    public function countUnpublishedAction()
    {
        $result = $this->em()->getRepository('AppBundle:Comment')
            ->countUnpublished();

        return new Response($result[1]);
    }
}
