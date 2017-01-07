<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Author;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('fullname', array($this, 'fullname')),
            new \Twig_SimpleFilter('preview', array($this, 'preview')),
        );
    }

    public function fullname(Author $author)
    {
        return $author->getFirstName().' '.$author->getLastName();
    }

    public function preview($str)
    {
        $str = substr($str, 0, 255);

        return trim($str).'...';
    }

    public function getName()
    {
        return 'app_extension';
    }
}
