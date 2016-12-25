<?php

namespace AppBundle\Form;

use AppBundle\Entity\Tag;
use AppBundle\Repository\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('content', TextareaType::class, array(
                'attr' => array('cols' => '5', 'rows' => '10')))
//            ->add('tagfsdfsdfs', CollectionType::class, array(
//                'entry_type'   => TagType::class,
//                'allow_add'    => true,
//                'by_reference' => false,
//            ))
            ->add('tags', EntityType::class,[
                'class' => Tag::class,
                'multiple' => true,
                'expanded' => true,
                'label_attr' => [
                        'class' => 'checkbox-inline']
                ]
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $article = $event->getData();
                $form = $event->getForm();

                if (!$article || null === $article->getId()) {
                    $form->add('imageFile', FileType::class);
                }
            })
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
