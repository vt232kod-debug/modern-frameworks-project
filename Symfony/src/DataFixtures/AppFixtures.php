<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Hall;
use App\Entity\Movie;
use App\Entity\Screening;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Demo data for filtering and pagination: php bin/console doctrine:fixtures:load
 */
class AppFixtures extends Fixture
{
    private const GENRES = ['Action', 'Comedy', 'Drama', 'Sci-Fi', 'Horror', 'Animation', 'Thriller', 'Documentary'];
    private const LANGUAGES = ['UA', 'EN', 'UA (subtitles)'];
    private const TITLES = [
        'Dune: Part Two', 'Oppenheimer', 'Interstellar', 'Inception', 'The Matrix', 'Toy Story', 'Shrek',
        'The Dark Knight', 'Pulp Fiction', 'Forrest Gump', 'Gladiator', 'Titanic', 'Avatar', 'Joker',
        'Parasite', 'The Godfather', 'Spirited Away', 'Coco', 'Up', 'Inside Out', 'Barbie', 'Alien',
        'Blade Runner 2049', 'Mad Max: Fury Road', 'The Shining', 'Get Out', 'La La Land', 'Whiplash',
        'Tenet', 'Arrival',
    ];

    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('uk_UA');
        $faker->seed(2026);

        $movies = [];
        foreach (self::TITLES as $title) {
            $movies[] = $movie = (new Movie())
                ->setTitle($title)
                ->setDescription($faker->realText(120))
                ->setGenre($faker->randomElement(self::GENRES))
                ->setDurationMinutes($faker->numberBetween(80, 190))
                ->setReleaseDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-5 years', '+1 month'))->setTime(0, 0))
                ->setAgeRating($faker->randomElement(['0+', '6+', '12+', '16+', '18+']));
            $manager->persist($movie);
        }

        $halls = [];
        foreach ([['Hall 1', '2D', 10, 12], ['Hall 2', '3D', 8, 10], ['IMAX', 'IMAX', 14, 20], ['VIP Lounge', 'VIP', 4, 6], ['Hall 5', '2D', 9, 14]] as [$name, $type, $rows, $seats]) {
            $halls[] = $hall = (new Hall())->setName($name)->setType($type)->setRowsCount($rows)->setSeatsPerRow($seats);
            $manager->persist($hall);
        }

        $screenings = [];
        for ($i = 0; $i < 60; $i++) {
            $startsAt = (new \DateTimeImmutable('today'))
                ->modify(sprintf('+%d days', $faker->numberBetween(-3, 14)))
                ->setTime($faker->randomElement([10, 12, 14, 16, 18, 20, 22]), $faker->randomElement([0, 30]));
            $screenings[] = $screening = (new Screening())
                ->setMovie($faker->randomElement($movies))
                ->setHall($faker->randomElement($halls))
                ->setStartsAt($startsAt)
                ->setPrice($faker->randomElement([150, 180, 200, 250, 320, 450]))
                ->setLanguage($faker->randomElement(self::LANGUAGES));
            $manager->persist($screening);
        }

        $customers = [];
        for ($i = 0; $i < 50; $i++) {
            $customers[] = $customer = (new Customer())
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setEmail($faker->unique()->safeEmail())
                ->setPhone($faker->optional(0.8)->numerify('+38050#######'))
                ->setBirthDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-60 years', '-14 years'))->setTime(0, 0));
            $manager->persist($customer);
        }

        $takenSeats = [];
        for ($i = 0; $i < 150; $i++) {
            $screening = $faker->randomElement($screenings);
            $hall = $screening->getHall();
            do {
                $row = $faker->numberBetween(1, $hall->getRowsCount());
                $seat = $faker->numberBetween(1, $hall->getSeatsPerRow());
                $key = spl_object_id($screening).":$row:$seat";
            } while (isset($takenSeats[$key]));
            $takenSeats[$key] = true;

            $manager->persist((new Ticket())
                ->setScreening($screening)
                ->setCustomer($faker->randomElement($customers))
                ->setSeatRow($row)
                ->setSeatNumber($seat)
                ->setPrice($screening->getPrice())
                ->setStatus($faker->randomElement(['reserved', 'paid', 'paid', 'paid', 'cancelled'])));
        }

        // Demo accounts, one per role; the client owns customer #1
        foreach ([
            ['admin@cinema.test', 'admin123', User::ROLE_ADMIN, null],
            ['manager@cinema.test', 'manager123', User::ROLE_MANAGER, null],
            ['client@cinema.test', 'client123', User::ROLE_CLIENT, $customers[0]],
        ] as [$email, $password, $role, $customer]) {
            $user = (new User())->setEmail($email)->setRole($role)->setCustomer($customer);
            $manager->persist($user->setPassword($this->hasher->hashPassword($user, $password)));
        }

        $manager->flush();
    }
}
