<?php

namespace AppBundle\Controller;

use AppBundle\Form\TagType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends BaseController
{
    /**
     * @param Request $request
     * @Route("/article/new/tag", name="new_tag")
     *
     * @return Response
     */
    public function createNewAction(Request $request)
    {
        $form = $this->createForm(TagType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tag = $form->getData();

            $this->em()->persist($tag);

            $this->em()->flush();

            $this->get('app.notifier')
                ->newTagNotify();

            return $this->redirectToRoute('new_article');
        }

        $tags = $this->em()->getRepository('AppBundle:Tag')
            ->findAll();

        return $this->render(':Forms:Tag.html.twig', [
            'Form' => $form->createView(),
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
