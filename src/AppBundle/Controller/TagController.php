<?php

namespace AppBundle\Controller;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends BaseController
{
    /**
     * @param Request $request
     * @Route("/admin/article/new/tag", name="new_tag")
     *
     * @return Response
     */
    public function createNewAction(Request $request)
    {
        $tagResult = $this->get('app.form_manager')
            ->createTagForm($request);

        $tags = $this->entityManager->getRepository('AppBundle:Tag')
            ->findAll();

        if (!$tagResult instanceof Form) {
            return $this->redirect($tagResult);
        }

        return $this->render(':Forms:Tag.html.twig', [
            'Form' => $tagResult->createView(),
            'tags' => $tags,
        ]);
    }

    /**
     * @Route("/article/tag/{tag}/{page}", name="tags", requirements={"page": "\d+"})
     *
     * @param Request $request
     * @param $tag
     * @param int $page
     *
     * @return Response
     */
    public function showAction(Request $request, $tag, $page = 1)
    {
        $tag = $this->em()->getRepository('AppBundle:Tag')
            ->findByTag($tag);

        if (!$tag) {
            throw new NotFoundHttpException();
        }
        $pagination = $this->pagination($tag->getArticles(), $request->query->getInt('page', $page), 5);

        return $this->render('Article/list.html.twig', [
            'articles' => $pagination,
        ]);
    }
}
