<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Faq;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FaqType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, [
                'required' => true, // This makes the field required
            ])
            ->add('answer', TextType::class, [
                'required' => true, // This makes the field required
            ])
            ->remove('cdate', null, [
                'widget' => 'single_text',
            ])
            ->remove('udate', null, [
                'widget' => 'single_text',
            ])
            ->add('category', EntityType::class, [
                        'class' => Category::class,
                        'choice_label' => 'title',
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                ->where('c.type = :type')
                                ->andWhere('c.published = :published')
                                ->setParameter('type', 3)
                                ->setParameter('published', 1);
                        },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Faq::class,
        ]);
    }
}
