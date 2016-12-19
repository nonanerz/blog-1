<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 */
abstract class Paragraph
{
    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Author")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    protected $content;
}
