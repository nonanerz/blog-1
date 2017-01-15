<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends Controller
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
        $em = $this->getDoctrine()->getManager();

        $tags = $em->getRepository('AppBundle:Tag')
            ->findAll();

        if (!$tagResult instanceof Form) {
            return $this->redirect($tagResult);
        }

        return $this->render(':Forms:Tag.html.twig', [
            'Form' => $tagResult->createView(),
            'tags' => $tags,
        ]);
    }
}
