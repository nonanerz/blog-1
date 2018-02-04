<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Form\ArticleType;
use AppBundle\Security\ArticleVoter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends BaseController
{
    /**
     * @param $request
     * @Route("/article/new", name="new_article")
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(ArticleType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();

            $this->denyAccessUnlessGranted(ArticleVoter::CREATE_ARTICLE, $article);
            if (!$article->getImage()) {
                $article->setImage('50708_1280x720-318x180.jpg');
            }

            $article->setAuthor($this->getUser()
                ->getAuthor()
            );

            $this->em()->persist($article);

            $this->em()->flush();

            $this->get('app.notifier')
                ->newArticleNotify($article, $article->getAuthor());

            return $this->redirectToRoute('homepage');
        }

        return $this->render(':Article:new.html.twig', [
            'articleType' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{page}", name="homepage", requirements={"page": "\d+"})
     *
     * @param $request
     * @param $page
     *
     * @return Response
     */
    public function listAction(Request $request, $page = 1)
    {
        $articles = $this->em()->getRepository('AppBundle:Article')
            ->findAllPublished();

        if (!$articles) {
            throw new NotFoundHttpException('Noooo!');
        }

        $pagination = $this->pagination($articles,
            $request->query->getInt('page', $page), 5);

        return $this->render('Article/list.html.twig', [
        'articles' => $pagination,
    ]);
    }

    /**
     * @param $request
     * @param $article
     * @Route("/article/edit/{id}", name="edit_article")
     *
     * @return Response
     */
    public function editAction(Request $request, Article $article)
    {
        $this->denyAccessUnlessGranted(ArticleVoter::EDIT_ARTICLE, $article);

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();

            if (!$article->getImage()) {
                $article->setImage('50708_1280x720-318x180.jpg');
            }

            $article->setAuthor($this->getUser()
                ->getAuthor()
            );

            $this->em()->persist($article);

            $this->em()->flush();

            return $this->redirectToRoute('show_article', ['id' => $article->getId()]);
        }

        return $this->render(':Article:edit.html.twig', [
            'articleType' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Article $article
     * @Route("/remove_comment/{article}", name="remove_article")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function deleteAction(Request $request, Article $article)
    {

        /** @var Form $form */
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('remove_article', [
                'article' => $article->getId(),
            ]))
            ->setMethod('DELETE')
            ->getForm();

        if ($request->isMethod('DELETE')) {
            $this->denyAccessUnlessGranted(ArticleVoter::DELETE_ARTICLE, $article);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->em()->remove($article);
                $this->em()->flush();

                return $this->redirectToRoute('homepage');
            }
        }

        return $this->render(':Forms:deleteArticle.html.twig', [
            'removeArticleType' => $form->createView(),
        ]);
    }

    /**
     * @param Request      $request
     * @param Article|null $article
     * @param int          $page
     * @Route("/article/{id}/{page}", name="show_article", requirements={"id": "\d+", "page": "\d+"})
     *
     * @return Response
     */
    public function showAction(Request $request, Article $article = null, $page = 1)
    {
        /** @var Article $article */
        $article = $this->em()->getRepository('AppBundle:Article')
            ->findByIdWithJoins($article);

        if (!$article) {
            throw new NotFoundHttpException('Article is not exist!');
        }

        $comments = $article->getComments();

        $pagination = $this->pagination($comments, $request->query->getInt('page', $page), 5);

        return $this->render('Article/article.html.twig', [
            'article' => $article,
            'comments' => $pagination,
        ]);
    }

    /**
     * @return Response
     */
    public function topArticlesAction()
    {
        $articles = $this->em()->getRepository('AppBundle:Article')
           ->findTopFive();

        return $this->render(':Article:top.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/search", name="search")
     *
     * @param Request $request
     * @param int     $page
     *
     * @return Response
     */
    public function searchAction(Request $request, $page = 1)
    {
        $result = $this->em()->getRepository('AppBundle:Article')
            ->search($request->query->get('q'));
        if (!$result) {
            $this->addFlash('failure', 'Nothing found');

            return $this->listAction($request);
        } else {
            $pagination = $this->pagination($result, $request->query->getInt('page', $page), 5);
        }

        return $this->render('Article/list.html.twig', [
            'articles' => $pagination,
        ]);
    }

    /**
     * @param Article $article
     * @Route("/article/{id}/like", name="article_like")
     *
     * @return Response
     */
    public function likeAction(Article $article)
    {
        if (!$this->isGranted('ROLE_USER')) {
            return;
        }
        $voices = $article->getVoices();
        $article->setVoices($voices + 1);
        $this->em()->persist($article);
        $this->em()->flush();

        return new Response($article->getVoices());
    }

    /**
     * @param Request $request
     * @param int     $page
     * @Route("/admin/articles/{page}", name="check_articles")
     *
     * @return Response
     */
    public function checkArticleAction(Request $request, $page = 1)
    {
        $pagination = $this->pagination($this->em()->getRepository('AppBundle:Article')
                ->findAllUnpublished(), $request->query->getInt('page', $page), 10);

        return $this->render('Admin/articles.html.twig', [
            'articles' => $pagination,
        ]);
    }

    /**
     * @param Article $article
     * @Route("/admin/articles/status/{id}", name="article_allowed")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function allowedAction(Article $article)
    {
        if ($article->getIsPublished()) {
            $article->setIsPublished(false);
        } else {
            $article->setIsPublished(true);
        }

        $this->em()->persist($article);

        $this->em()->flush();

        return $this->redirectToRoute('check_articles');
    }

    /**
     * @return Response
     */
    public function countUnpublishedAction()
    {
        $result = $this->em()->getRepository('AppBundle:Article')
            ->countUnpublished();

        return new Response($result[1]);
    }
}
