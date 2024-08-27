<?php

namespace App\Form;

use App\Entity\ContactUs;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactUsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('fullName')
            ->remove('text')
            ->remove('cdate', null, [
                'widget' => 'single_text',
            ])
            ->remove('published')
            ->remove('type')
            ->remove('subject')
            ->remove('mobile')
            ->remove('email')
            ->remove('remove')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactUs::class,
        ]);
    }
}
