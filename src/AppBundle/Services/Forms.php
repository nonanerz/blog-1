<?php

namespace AppBundle\Services;


use AppBundle\Entity\Article;
use AppBundle\Entity\Comment;
use AppBundle\Form\CommentType;
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

    public function newCommentForm(Request $request, Article $article)
    {
        $form = $this->formFactory->create(CommentType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->doctrine->getManager();

            $comment = $form->getData();

            $author = $em->getRepository('AppBundle:Author')
                ->find(11);

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

    public function removeComment(Request $request, Comment $comment, Article $article)
    {
        $builder = $this->formFactory->createBuilder();
        $form = $builder
            ->setAction($this->router->generate('remove_comment', [
                'comment' => $comment->getId(),
                'article' => $article->getId()
            ]))
            ->setMethod('DELETE')
            ->getForm();
        $this->notifier->removeCommentNotify();
        return $form->handleRequest($request);
    }

    public function removeArticle(Request $request, Article $article)
    {

        $builder = $this->formFactory->createBuilder();
        $form = $builder
            ->setMethod('DELETE')
            ->getForm();

        $form->handleRequest($request);

        return $form;
    }
}