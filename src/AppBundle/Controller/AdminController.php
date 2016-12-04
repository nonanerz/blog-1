<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends Controller
{
    /**
     * @Route("/admin/comments", name="check_comments")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkCommentsAction()
    {
        $em = $this->getDoctrine()->getManager();

        $comments = $em->getRepository('AppBundle:Comment')
            ->findAll();

        return $this->render(':Admin:comments.html.twig', [
            'comments' => $comments,
        ]);
    }

    /**
     * @Route("/about", name="about_me")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function aboutMeAction()
    {
        return $this->render('about.html.twig');
    }
}
