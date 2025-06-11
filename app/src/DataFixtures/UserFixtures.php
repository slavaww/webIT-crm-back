<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;
    
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $admin = new User();
        $admin->setEmail('s@u-wr.ru');
        $admin->setRoles(['ROLE_SUPER_ADMIN']);
        
        $password = $this->hasher->hashPassword($admin, 'sl1234567wr');
        $admin->setPassword($password);
        
        $manager->persist($admin);

        $manager->flush();
    }
}
