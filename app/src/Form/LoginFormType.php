<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'autocomplete' => 'email',
                    'class' => 'form-control',
                    'placeholder' => 'your@email.com'
                ],
                'label' => 'Email',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'form.error.email_required']),
                    new Assert\Email(['message' => 'form.error.invalid_email']),
                ],
            ])
            ->add('password', PasswordType::class, [
                'attr' => [
                    'autocomplete' => 'current-password',
                    'class' => 'form-control',
                    'placeholder' => '••••••••',
                ],
                'label' => 'Пароль',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'form.error.password_required']),
                ],
            ])
            ->add('_remember_me', CheckboxType::class, [
                'required' => false,
                'label' => 'Запомнить меня',
            ]);
    }
}
