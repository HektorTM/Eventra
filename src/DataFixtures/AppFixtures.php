<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $profile1 = new UserProfile();
        $profile1->setDisplayName('HektorTM');
        $profile1->setAvatarUrl('https://lh3.googleusercontent.com/a/ACg8ocKSw8-sqIlhHs6pD_mKZa0gOolYn5TEkg4HKUsOrn2m5JQf5Tzk=s192-c-mo 2x');

        $user1 = new User();
        $user1->setUsername('Paul von Berg');
        $user1->setEmail('paul.vonberg@eventra.com');
        $user1->setPassword(password_hash('password', PASSWORD_DEFAULT));
        $user1->setRoles(['ROLE_ADMIN']);
        $user1->setIsActive(true);
        $user1->setCreatedAt(new \DateTimeImmutable('now'));
        $user1->setUserProfile($profile1);
        $profile1->setUser($user1);

        $user2 = new User();
        $user2->setUsername('Hektor Crestello');
        $user2->setEmail('hektor.crestello@eventra.com');
        $user2->setPassword(password_hash('password', PASSWORD_DEFAULT));
        $user2->setRoles(['ROLE_PUBLISHER']);
        $user2->setIsActive(true);
        $user2->setCreatedAt(new \DateTimeImmutable('now'));

        $category1 = new Category();
        $category1->setName('Tech');

        $category2 = new Category();
        $category2->setName('Design');

        $event1 = new Event();
        $event1->setSlug('symfony-meetup');
        $event1->setTitle('Symfony Community Meetup');
        $event1->setDate(new \DateTime('2026-02-12 19:00'));
        $event1->setLocation('Berlin, Germany');
        $event1->setImage('https://images.unsplash.com/photo-1540575467063-178a50c2df87');
        $event1->setDescription('A friendly meetup for Symfony developers.');
        $event1->setCategory($category1);
        $event1->setCreatedBy($user1);
        $event1->setIsPublished(false);

        $event2 = new Event();
        $event2->setSlug('php-conference');
        $event2->setTitle('Modern PHP Conference');
        $event2->setDate(new \DateTime('2026-03-03 09:00'));
        $event2->setLocation('Amsterdam, Netherlands');
        $event2->setImage('https://images.unsplash.com/photo-1503428593586-e225b39bddfe');
        $event2->setDescription('A full-day conference about modern PHP.');
        $event2->setCategory($category2);
        $event2->setCreatedBy($user2);
        $event2->setIsPublished(true);

        $category1->addEvent($event1);
        $category2->addEvent($event2);

        $manager->persist($user1);
        $manager->persist($user2);
        $manager->persist($category1);
        $manager->persist($category2);
        $manager->persist($event1);
        $manager->persist($event2);

        $manager->flush();
    }
}
