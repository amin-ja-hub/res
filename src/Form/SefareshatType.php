<?php

namespace App\Form;

use App\Entity\Sefareshat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SefareshatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price')
            ->add('status')
            ->add('cdate', null, [
                'widget' => 'single_text',
            ])
            ->add('paymentStatus')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sefareshat::class,
        ]);
    }
}
