<?php

namespace AppBundle\Form;

use AppBundle\Entity\Tag;
use AppBundle\Repository\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('content', TextareaType::class, array(
                'attr' => array('cols' => '5', 'rows' => '10')))
            ->add('Tags', EntityType::class, array(
                'class' => Tag::class,
                'query_builder' => function (TagRepository $repository){
                    return $repository->createQueryBuilder('tag')
                        ->orderBy('tag.title', 'ASC');
                },
                'expanded' => true,
                'multiple' =>true
            ))
            ->add('imageFile', FileType::class)

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Article',
            'attr' => ['novalidate' => 'novalidate'],

        ]);
    }

    public function getName()
    {
        return 'app_bundle_article_type';
    }
}
