<?php

namespace AppBundle\Controller;

use AppBundle\Form\AuthorizationType;
use AppBundle\Form\AuthorRegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @Route("/registration", name="registration")
     *
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(AuthorRegistrationType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $author = $form->getData();

            if (!$author->getImageName()) {
                $author->setImageName('avatar.png');
            }

            $em->persist($author);

            $em->persist($author->getUser());

            $em->flush();

            $this->addFlash('success', 'Registration completed');

            return $this->redirectToRoute('homepage', [], 201);
        }

        return $this->render('Forms/Registration.html.twig', [
            'userType' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @Route("/sign-in", name="login")
     *
     * @return Response
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
     * @return Response
     */
    public function aboutMeAction()
    {
        return $this->render('about.html.twig');
    }

    /**
     * @return Response
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
