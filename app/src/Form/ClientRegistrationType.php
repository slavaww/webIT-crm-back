<?php

namespace App\Form;

use App\Entity\Clients;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Название клиента (компания или ФИО)',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание',
                'required' => false,
            ])
            ->add('job_title', TextType::class, [
                'label' => 'Должность',
                'required' => false,
            ])
            ->add('phone', TextType::class, [
                'label' => 'Телефон',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Контактный Email',
                'required' => false,
            ])
            ->add('user_id', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Связать с пользователем',
                'placeholder' => 'Выберите пользователя',
                'required' => true,
                'query_builder' => function (UserRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('u');

                    // Подзапрос для выбора ID пользователей, уже связанных с клиентами
                    $subQuery = $er->getEntityManager()->createQueryBuilder()
                        ->select('IDENTITY(c.user_id)')
                        ->from(Clients::class, 'c')
                        ->where('c.user_id IS NOT NULL');

                    // Если редактируем, исключаем текущего пользователя из подзапроса
                    if ($options['current_client_user_id']) {
                        $subQuery->andWhere('c.user_id != :current_user_id');
                    }

                    $qb->where('u.roles LIKE :role')
                       ->andWhere($qb->expr()->notIn('u.id', $subQuery->getDQL()))
                       ->setParameter('role', '%"ROLE_USER"%');

                    // Устанавливаем параметр для подзапроса на уровне основного запроса
                    if ($options['current_client_user_id']) {
                        $qb->setParameter('current_user_id', $options['current_client_user_id']);
                    }

                    $qb->orderBy('u.email', 'ASC');

                    return $qb;
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Clients::class,
            // Добавляем опцию для передачи ID текущего пользователя
            'current_client_user_id' => null,
        ]);
    }
}
