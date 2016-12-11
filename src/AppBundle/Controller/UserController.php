<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
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
