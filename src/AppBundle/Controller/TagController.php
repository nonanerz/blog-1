<?php

namespace AppBundle\Controller;

use AppBundle\Form\TagType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends Controller
{
    /**
     * @param Request $request
     * @Route("/article/new/tag", name="new_tag")
     * @return Response
     */
    public function createNewAction(Request $request)
    {
        $form = $this->createForm(TagType::class);

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $tags = $em->getRepository('AppBundle:Tag')
            ->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $tag = $form->getData();

            $em->persist($tag);

            $em->flush();

            return $this->redirectToRoute('new_article');
        }

        return $this->render(':Forms:Tag.html.twig', [
            'Form' => $form->createView(),
            'tags' => $tags
        ]);
    }
}