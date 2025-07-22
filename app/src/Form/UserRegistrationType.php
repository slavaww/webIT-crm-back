<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Form\DataTransformer\RoleToArrayTransformer;
use Symfony\Component\Validator\Constraints as Assert;

class UserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['autocomplete' => 'email'],
                'label' => 'Email'
            ])
            ->add('password', PasswordType::class, [
            'attr' => ['autocomplete' => 'new-password'],
            'label' => $options['is_edit'] ? 
                'Новый пароль (оставьте пустым, чтобы не менять)' : 
                'Пароль',
            'required' => !$options['is_edit'],
            'mapped' => false,
            'constraints' => $options['is_edit'] ? [] : [
                new Assert\NotBlank(['message' => 'Пароль обязателен']),
                new Assert\Length(['min' => 8]),
                new Assert\PasswordStrength([
                    'minScore' => Assert\PasswordStrength::STRENGTH_WEAK,
                    'message' => 'Пароль слишком простой'
                ])
            ]])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Клиент' => 'ROLE_USER',
                    'Сотрудник' => 'ROLE_ADMIN',
                    'Администратор' => 'ROLE_SUPER_ADMIN',
                ],
                'label' => 'Роль',
                'placeholder' => 'Выберите роль',
                'required' => true,
            ]);
        // Добавляем трансформер к полю roles
        $builder->get('roles')
            ->addModelTransformer(new RoleToArrayTransformer());

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}