<?php

namespace AppBundle\Controller;

use AppBundle\Form\AuthorizationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;

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
        $newUserResult = $this->get('app.form_manager')
            ->createNewUserForm($request);

        if (!$newUserResult instanceof Form) {

            return $this->redirect($newUserResult);
        }

        return $this->render('Forms/Registration.html.twig', [
            'userType' => $newUserResult->createView(),
        ]);
    }

    /**
     * @Route("/login", name="security_login")
     *
     * @return Response
     */
    public function authorizationAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error !== null){
            $this->addFlash('failure', $error->getMessageKey());
        }
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(AuthorizationType::class, [
            '_username' => $lastUsername
        ]);

        return $this->render(':Forms:authorization.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logoutAction()
    {

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
