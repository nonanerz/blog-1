<?php

namespace AppBundle\Services;

use AppBundle\Entity\Article;
use AppBundle\Entity\Comment;
use AppBundle\Form\ArticleType;
use AppBundle\Form\AuthorRegistrationType;
use AppBundle\Form\CommentType;
use AppBundle\Form\TagType;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class Forms
{
    protected $formFactory;
    protected $doctrine;
    protected $router;
    protected $notifier;

    public function __construct(FormFactoryInterface $formFactory,
                                RegistryInterface $doctrine,
                                RouterInterface $router,
                                Notifier $notifier)
    {
        $this->formFactory = $formFactory;
        $this->doctrine = $doctrine;
        $this->router = $router;
        $this->notifier = $notifier;
    }

    public function createArticleForm(Request $request, Article $article = null)
    {
        $form = $this->formFactory->create(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->doctrine->getManager();

            $article = $form->getData();

            if (!$article->getImage()) {
                $article->setImage('50708_1280x720-318x180.jpg');
            }

            $article->setAuthor($em->getRepository('AppBundle:Author')
                ->find(1));

            $em->persist($article);

            $em->flush();

            $this->notifier
                ->newArticleNotify($article, $article->getAuthor());

            return $this->router->generate('homepage');
        }

        return $form;
    }

    public function newCommentForm(Request $request, Article $article)
    {
        $form = $this->formFactory->create(CommentType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->doctrine->getManager();

            $comment = $form->getData();

            $author = $em->getRepository('AppBundle:Author')
                ->find(1);

            $comment->setAuthor($author);

            $comment->setArticle($article);

            $article->addComment($comment);

            $em->persist($article);
            $em->persist($comment);

            $em->flush();
            $this->notifier->newCommentNotify($comment, $author, $article->getAuthor());

            return $this->router->generate('show_article', ['id' => $article->getId()]);
        }

        return $form;
    }

    public function removeCommentForm(Request $request, Comment $comment, Article $article)
    {
        $builder = $this->formFactory->createBuilder();
        $form = $builder
            ->setAction($this->router->generate('remove_comment', [
                'comment' => $comment->getId(),
                'article' => $article->getId(),
            ]))
            ->setMethod('DELETE')
            ->getForm();
        $this->notifier->removeCommentNotify();

        return $form->handleRequest($request);
    }

    public function removeArticleForm(Request $request, Article $article)
    {
        $em = $this->doctrine->getManager();
        $builder = $this->formFactory->createBuilder();
        $form = $builder
            ->setMethod('DELETE')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->remove($article);
            $em->flush();
            $this->notifier->removeArticleNotify();
            return $this->router->generate('homepage');
        }
        return $form;
    }

    public function createTagForm(Request $request)
    {
        $form = $this->formFactory->create(TagType::class);

        $form->handleRequest($request);

        $em = $this->doctrine->getManager();

        if ($form->isSubmitted() && $form->isValid()) {

            $tag = $form->getData();

            $em->persist($tag);

            $em->flush();

            $this->notifier->newTagNotify();

            return $this->router->generate('new_article');
        }

        return $form;
    }

    public function createNewUserForm(Request $request)
    {
        $form = $this->formFactory->create(AuthorRegistrationType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->doctrine->getManager();

            $author = $form->getData();

            if (!$author->getImageName()) {
                $author->setImageName('avatar.png');
            }

            $em->persist($author);

            $em->persist($author->getUser());

            $em->flush();

            $this->notifier->newUserNotify();

            return $this->router->generate('homepage', [], 201);
        }

        return $form;
    }
}
