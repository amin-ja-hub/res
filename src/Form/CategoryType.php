<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\File;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('title')
            ->remove('url')
            ->remove('cdate', null, [
                'widget' => 'single_text',
            ])
            ->remove('udate', null, [
                'widget' => 'single_text',
            ])
            ->remove('published')
            ->remove('type')
            ->remove('metaKey')
            ->remove('metadesc')
            ->remove('remove')
            ->remove('parent', EntityType::class, [
                'class' => Category::class,
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
