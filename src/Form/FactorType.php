<?php

namespace App\Form;

use App\Entity\Factor;
use App\Entity\Sefareshat;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FactorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cdate', null, [
                'widget' => 'single_text',
            ])
            ->add('trackingCode')
            ->add('amount')
            ->add('sefareshat', EntityType::class, [
                'class' => Sefareshat::class,
                'choice_label' => 'id',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Factor::class,
        ]);
    }
}
