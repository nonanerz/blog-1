<?php

namespace AppBundle\Form;

use AppBundle\Entity\Author;
use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class AuthorRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', UserRegistrationType::class, [
            'label' => '* - Required fields',
            'constraints' => array(new Valid())
        ])
            ->add('firstName', TextType::class, [
            'label' => 'Name'
        ])
            ->add('lastName', TextType::class)
            ->add('imageFile', FileType::class, [
                'label' => 'Your avatar'
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            ['data_class' => Author::class,
             'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
