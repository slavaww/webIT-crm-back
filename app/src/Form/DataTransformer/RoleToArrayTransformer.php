<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class RoleToArrayTransformer implements DataTransformerInterface
{
    public function transform($rolesArray) :mixed
    {
        // Из entity в форму: берем первую роль из массива
        return is_array($rolesArray) ? ($rolesArray[0] ?? null) : null;
    }

    public function reverseTransform($roleString) :mixed
    {
        // Из формы в entity: возвращаем массив с одной ролью
        return $roleString ? [$roleString] : [];
    }
}