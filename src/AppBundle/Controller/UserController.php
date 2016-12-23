<?php

namespace AppBundle\Controller;

use AppBundle\Form\AuthorizationType;
use AppBundle\Form\AuthorRegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{

    /**
     * @param Request $request
     * @Route("/registration", name="registration")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        dump($request);

        $form = $this->createForm(AuthorRegistrationType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $author = $form->getData();

            $em->persist($author);

            $em->persist($author->getUser());

            $em->flush();

            $this->addFlash('success', 'Registration completed');


            return $this->redirectToRoute('homepage',[], 201);
        }

        return $this->render('Forms/Registration.html.twig', [
            'userType' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @Route("/sign-in", name="login")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function authorizationAction(Request $request)
    {
        $form = $this->createForm(AuthorizationType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


        }

        return $this->render(':Forms:authorization.html.twig', [
            'authorizationType' => $form->createView(),
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

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/admin/checkUsers", name="check_users")
     */
    public function usersListAction()
    {
        $em = $this->getDoctrine()->getManager();

        $authors = $em->getRepository('AppBundle:Author')
            ->findAllWithUsers();

        return $this->render(':Admin:users.html.twig', [
            'authors' => $authors,
        ]);
    }
}
