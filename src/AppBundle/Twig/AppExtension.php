<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Author;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('fullname', array($this, 'fullname')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('preview', array($this, 'preview'), array('is_safe' => array('html'))),
        );
    }

    public function fullname(Author $author)
    {
        return $author->getFirstName().' '.$author->getLastName();
    }

    public function preview($str, $length = 30)
    {
        $words = explode(' ', ($str));
        return implode(' ', array_slice($words, 0, $length)) . '...';



    }

    public function getName()
    {
        return 'app_extension';
    }
}
