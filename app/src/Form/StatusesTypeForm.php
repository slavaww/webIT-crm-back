<?php

namespace App\Form;

use App\Entity\Statuses;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Repository\StatusesRepository;

class StatusesTypeForm extends AbstractType
{
    private StatusesRepository $statusesRepository;

    public function __construct(StatusesRepository $statusesRepository)
    {
        $this->statusesRepository = $statusesRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', TextType::class, [
                'label' => 'Название статуса (например, "В работе", "Выполнена" и т.д.)',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Statuses::class,
            'current_status_id' => null,
        ]);
        $resolver->setAllowedTypes('current_status_id', ['null', 'int']);
    }

    public function getStatusEntity(int $id): ?Statuses
    {
        return $this->statusesRepository->find($id);
    }
}
