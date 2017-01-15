<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Author;
use AppBundle\Entity\User;
use AppBundle\Form\AuthorizationType;
use AppBundle\Form\AuthorRegistrationType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
        $form = $this->createForm(AuthorRegistrationType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Author $author */
            $author = $form->getData();

            $this->em()->persist($author);

            $this->em()->persist($author->getUser()->setRoles('ROLE_USER'));

            $this->em()->flush();

            return $this->get('security.authentication.guard_handler')
                ->authenticateUserAndHandleSuccess(
                    $author->getUser(),
                    $request,
                    $this->get('app.security.login_form_authenticator'),
                    'main'
                );
        }

        return $this->render('Forms/Registration.html.twig', [
            'userType' => $form->createView(),
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

    /**
     * @param Request $request
     * @param User    $user
     * @Route("/admin/users/roles/{id}", name="manage_roles")
     *
     * @return Response
     */
    public function roleManageAction(Request $request, User $user)
    {
        $form = $this->createFormBuilder()
               ->add('roles', ChoiceType::class, array(
                        'choices' => array(
                            'User' => 'ROLE_USER',
                            'Moderator' => 'ROLE_MODERATOR',
                            'Admin' => 'ROLE_ADMIN',
                        ), 'label' => false,
                    ))
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $role = $form->getData();

            $user->addRole($role['roles']);

            $this->em()->persist($user);

            $this->em()->flush();

            return $this->redirectToRoute('check_users');
        }

        return $this->render(':Forms:manageRoles.html.twig', [
            'form' => $form->createView(),
            'id' => $user->getId(),
        ]);
    }
}
