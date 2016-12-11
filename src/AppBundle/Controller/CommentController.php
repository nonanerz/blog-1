<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
        $paginator = $this->get('knp_paginator');

        $em = $this->getDoctrine()->getManager();
        $pagination = $paginator->paginate($em->getRepository('AppBundle:Comment')
            ->findAll(), $request->query->getInt('page', $page),
            10);

        return $this->render('Admin/comments.html.twig', [
            'comments' => $pagination,
        ]);
    }
}
