<?php

namespace App\Form;

use App\Entity\File;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\UserInformation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserInformationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('title')
            ->remove('text')
            ->remove('cDate', null, [
                'widget' => 'single_text',
            ])
            ->remove('uDate', null, [
                'widget' => 'single_text',
            ])
            ->remove('published')
            ->remove('type')
            ->remove('remove')
            ->remove('parent', EntityType::class, [
                'class' => UserInformation::class,
                'choice_label' => 'id',
            ])
            ->remove('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->remove('image', EntityType::class, [
                'class' => File::class,
                'choice_label' => 'id',
            ])
            ->remove('template', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserInformation::class,
        ]);
    }
}
