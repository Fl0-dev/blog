<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasherInterface;
    private $postRepository;
    private $categoryRepository;

    public function __construct (UserPasswordHasherInterface $userPasswordHasherInterface,PostRepository $postRepository, CategoryRepository $categoryRepository)
    {
        $this->userPasswordHasherInterface = $userPasswordHasherInterface;
        $this->postRepository = $postRepository;
        $this->categoryRepository = $categoryRepository;
    }


    public function load(ObjectManager $manager): void
    {

        $faker = Faker\Factory::create();

        $plaintextPassword = 'password';
        $categories = $this->categoryRepository->findAll();

        $posts = $this->postRepository->findAll();
        foreach ($posts as $post) {
            $category = array_rand($categories,1);
            $post->addCategory($this->categoryRepository->find(rand(1,16)));
            $manager->persist($post);
        }

        for ($i = 0; $i < 5; $i++) {
            $category = new Category();
            $category->setName($faker->text(20));
            $category->setDescription($faker->text(200));

            $manager->persist($category);
        }


        for ($i = 0; $i < 2; $i++) {
            $user = new User();
            $user->setFirstname($faker->firstName());
            $user->setLastname($faker->lastName());
            $user->setEmail($faker->email());
            $user->setPassword($this->userPasswordHasherInterface->hashPassword(
                $user, $plaintextPassword));
            $user->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTime('now')));

            for ($i = 0; $i < 25; $i++) {
                $post = new Post();
                $post->setTitle($faker->word());
                $post->setUser($user);
                $post->setContent($faker->text());
                $post->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTime('now')));
                $post->setUpdatedAt(\DateTimeImmutable::createFromMutable($faker->dateTime('now')));
                $post->setPublished(true);

                for ($i = 0; $i < 3; $i++) {
                    $category = new Category();
                    $category->setName($faker->text(20));
                    $category->setDescription($faker->text(100));

                    $manager->persist($category);
                }
                $post->addCategory($category);

                $manager->persist($post);
            }

            $manager->persist($user);
        }


        $manager->flush();
    }
}
