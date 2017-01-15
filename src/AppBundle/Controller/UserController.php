<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\AuthorizationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;

class UserController extends BaseController
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
            $this->addFlash('success', "Welcome {$newUserResult->getUsername()}!");

            return $this->get('security.authentication.guard_handler')
                ->authenticateUserAndHandleSuccess(
                    $newUserResult,
                    $request,
                    $this->get('app.security.login_form_authenticator'),
                    'main'
                );
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

        if ($error !== null) {
            $this->addFlash('failure', $error->getMessageKey());
        }
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(AuthorizationType::class, [
            '_username' => $lastUsername,
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
     * @Route("/admin/users", name="check_users")
     */
    public function usersListAction()
    {
        $authors = $this->em()->getRepository('AppBundle:Author')
            ->findAllWithUsers();

        return $this->render(':Admin:users.html.twig', [
            'authors' => $authors,
        ]);
    }

    /**
     * @param User $user
     * @Route("/admin/users/status/{id}", name="user_lock")
     *
     * @return Response
     */
    public function allowedAction(User $user)
    {
        if ($user->getIsActive()) {
            $user->setIsActive(false);
        } else {
            $user->setIsActive(true);
        }

        $this->em()->persist($user);

        $this->em()->flush();

        return $this->redirectToRoute('check_users');
    }
}
