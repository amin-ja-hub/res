<?php

namespace App\Form;

use App\Entity\File;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('fullName')
            ->remove('username')
            ->remove('cdate', null, [
                'widget' => 'single_text',
            ])
            ->remove('udate', null, [
                'widget' => 'single_text',
            ])
            ->remove('published')
            ->remove('role')
            ->remove('remove')
            ->remove('image', EntityType::class, [
                'class' => File::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
