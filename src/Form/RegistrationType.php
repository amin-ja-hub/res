<?php
// src/Form/RegistrationFormType.php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3, 'max' => 255]),
                ],
                'attr' => [
                    'class' => 'form-control', // Add your custom class here
                ],
            ])
            ->remove('fullName', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'custom-class', // Add your custom class here
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'رمز عبور',
                    'attr' => [
                        'class' => 'form-control', // Add your custom class here
                    ],
                ],
                'second_options' => [
                    'label' => 'تکرار رمز عبور',
                    'attr' => [
                        'class' => 'form-control', // Add your custom class here
                    ],
                ],
                'invalid_message' => 'تکرار رمز عبور درست نمی باشد.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
