<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        for($i = 0 ; $i<14; $i++)
        {
            $user = new User();
            $encoded = $this->encoder->encodePassword($user, $faker->password);
            $user->setName($faker->firstName())
                ->setPassword($encoded)
                ->setMail($faker->freeEmail);
            $manager->persist($user);
            $manager->flush();
        }
    }
}
