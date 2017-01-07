<?php

namespace AppBundle\Services;


use AppBundle\Entity\Article;
use AppBundle\Entity\Author;
use AppBundle\Entity\Comment;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class Notifier
{
    protected $mailer;

    protected $logger;

    protected $flashBag;

    public function __construct(\Swift_Mailer $mailer, LoggerInterface $logger, FlashBagInterface $flashBag)
    {
        $this->mailer = $mailer;

        $this->logger = $logger;

        $this->flashBag = $flashBag;
    }

    public function newCommentNotify(Comment $comment, Author $author, Author $articleAuthor)
    {
        $authorMessage = \Swift_Message::newInstance()
            ->setSubject('New comment')
            ->setFrom('bulavaeduard@gmail.com')
            ->setTo($author->getUser()->getEmail())
            ->setBody($comment->getContent());
        $articleAuthorMessage = \Swift_Message::newInstance()
            ->setSubject('New comment')
            ->setFrom('bulavaeduard@gmail.com')
            ->setTo($articleAuthor->getUser()->getEmail())
            ->setBody('Your article was commented. ' . $comment->getContent());

        $this->mailer->send($authorMessage);
        $this->mailer->send($articleAuthorMessage);

        $this->flashBag->add('success', 'New comment was created');

        $this->logger->info('new comment was created: ' . $comment->getContent());
    }

    public function newArticleNotify(Article $article, Author $author)
    {
        $authorMessage = \Swift_Message::newInstance()
            ->setSubject('New article')
            ->setFrom('bulavaeduard@gmail.com')
            ->setTo($author->getUser()->getEmail())
            ->setBody($article->getContent());

        $this->mailer->send($authorMessage);

        $this->flashBag->add('success', 'New article was created!');

        $this->logger->info('new article was created: ' . $article->getContent());
    }

    public function removeCommentNotify()
    {

    }

    public function removeArticleNotify()
    {

    }

    public function editArticleNotify()
    {
        $this->flashBag->add('success', 'Changes saved!');

    }

}