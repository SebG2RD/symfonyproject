<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $toImmutable = static function (\DateTimeInterface $date): \DateTimeImmutable {
            if ($date instanceof \DateTimeImmutable) {
                return $date;
            }

            return \DateTimeImmutable::createFromMutable($date);
        };

        // Créer un administrateur
        $admin = new User();
        $admin->setEmail('admin@blog.fr');
        $admin->setFirstName('Admin');
        $admin->setLastName('Utilisateur');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsActive(true);
        $admin->setProfilePicture('https://i.pravatar.cc/300?img=1');
        $adminCreatedAt = $toImmutable($faker->dateTimeBetween('-2 years', '-6 months'));
        $admin->setCreatedAt($adminCreatedAt);
        $admin->setUpdatedAt($toImmutable($faker->dateTimeBetween($adminCreatedAt->format('Y-m-d H:i:s'), 'now')));
        $manager->persist($admin);

        // Créer des utilisateurs réguliers
        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail('user' . $i . '@blog.fr');
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            $user->setRoles(['ROLE_USER']);
            $user->setIsActive($faker->boolean(90));
            $user->setProfilePicture('https://i.pravatar.cc/300?img=' . ($i + 1));
            $createdAt = $toImmutable($faker->dateTimeBetween('-2 years', '-1 month'));
            $user->setCreatedAt($createdAt);
            if ($faker->boolean(70)) {
                $user->setUpdatedAt($toImmutable($faker->dateTimeBetween($createdAt->format('Y-m-d H:i:s'), 'now')));
            }
            $manager->persist($user);
            $users[] = $user;
        }

        // Créer des catégories
        $categoryNames = ['Technologie', 'Développement Web', 'PHP', 'Symfony', 'Base de données', 'Sécurité'];
        $categories = [];
        foreach ($categoryNames as $name) {
            $category = new Category();
            $category->setName($name);
            $category->setDescription($faker->paragraph());
            $manager->persist($category);
            $categories[] = $category;
        }

        // Créer des posts
        $posts = [];
        for ($i = 0; $i < 20; $i++) {
            $post = new Post();
            $post->setTitle($faker->sentence(6));
            $post->setContent($faker->paragraphs(5, true));
            $post->setPicture('https://picsum.photos/seed/post-' . ($i + 1) . '/800/450');
            $post->setAuthor($users[array_rand($users)]);
            $post->setCategory($categories[array_rand($categories)]);
            $post->setPublishedAt($toImmutable($faker->dateTimeBetween('-1 year', 'now')));
            $manager->persist($post);
            $posts[] = $post;
        }

        // Créer des commentaires
        $commentStatuses = ['pending', 'approved', 'rejected'];
        for ($i = 0; $i < 80; $i++) {
            $comment = new Comment();
            $comment->setContent($faker->paragraph(2));
            $comment->setAuthor($users[array_rand($users)]);
            $post = $posts[array_rand($posts)];
            $comment->setPost($post);
            $comment->setStatus($faker->randomElement($commentStatuses));
            $comment->setCreatedAt($toImmutable($faker->dateTimeBetween($post->getPublishedAt()->format('Y-m-d H:i:s'), 'now')));
            $manager->persist($comment);
        }

        $manager->flush();
    }
}
