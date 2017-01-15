<?php

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class BaseController extends Controller
{

    public function em()
    {
        return $this->getDoctrine()->getManager();
    }

    public function pagination($page = 1, $limit = 10, array $options = array())
    {
        return $this->get('knp_paginator')->paginate($page = 1, $limit = 10, $options = array());
    }

}