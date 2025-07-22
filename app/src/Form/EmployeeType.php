<?php

namespace App\Form;

use App\Entity\Employee;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('job_title', TextType::class, [
                'label' => 'Должность',
                'required' => true,
            ])
            ->add('user_id', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Пользователь',
                'placeholder' => 'Выберите пользователя',
                'required' => true,
                'query_builder' => function (UserRepository $er) use ($options) {
                    // Создаем основной построитель запроса
                    $qb = $er->createQueryBuilder('u');

                    // Создаем подзапрос для выбора ID уже занятых пользователей
                    $subQuery = $er->getEntityManager()->createQueryBuilder()
                        ->select('IDENTITY(e.user_id)')
                        ->from(Employee::class, 'e')
                        ->where('e.user_id IS NOT NULL');

                    // Если это форма редактирования, добавляем условие в DQL подзапроса,
                    // но НЕ устанавливаем параметр здесь.
                    if ($options['current_employee_user_id']) {
                        $subQuery->andWhere('e.user_id != :current_user_id');
                    }

                    // Собираем основной запрос
                    $qb->where('u.roles LIKE :role')
                       // Используем DQL подзапроса
                       ->andWhere($qb->expr()->notIn('u.id', $subQuery->getDQL()))
                       // Устанавливаем первый параметр (:role) на основном построителе
                       ->setParameter('role', '%"ROLE_ADMIN"%')
                       ->setParameter('role', '%"ROLE_SUPER_ADMIN"%');

                    // Устанавливаем второй параметр (:current_user_id)
                    // также НА ОСНОВНОМ построителе.
                    if ($options['current_employee_user_id']) {
                        $qb->setParameter('current_user_id', $options['current_employee_user_id']);
                    }

                    $qb->orderBy('u.email', 'ASC');

                    return $qb;
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employee::class,
            'current_employee_user_id' => null,
        ]);
    }
}
