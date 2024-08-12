<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\File;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('title')
            ->remove('text')
            ->remove('cdate', null, [
                'widget' => 'single_text',
            ])
            ->remove('udate', null, [
                'widget' => 'single_text',
            ])
            ->remove('metaKey')
            ->remove('metadesc')
            ->remove('published')
            ->remove('type')
            ->remove('url')
            ->remove('bazdid')
            ->remove('remove')
            ->remove('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->remove('image', EntityType::class, [
                'class' => File::class,
                'choice_label' => 'id',
            ])
            ->remove('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
