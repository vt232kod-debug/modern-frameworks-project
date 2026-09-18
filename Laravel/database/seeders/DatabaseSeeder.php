<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Screening;
use App\Models\Ticket;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

/**
 * Demo data for filtering and pagination: php artisan migrate:fresh --seed
 */
class DatabaseSeeder extends Seeder
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

    public function run(): void
    {
        $faker = Factory::create('uk_UA');
        $faker->seed(2026);

        $movies = collect(self::TITLES)->map(fn (string $title) => Movie::create([
            'title' => $title,
            'description' => $faker->realText(120),
            'genre' => $faker->randomElement(self::GENRES),
            'duration_minutes' => $faker->numberBetween(80, 190),
            'release_date' => $faker->dateTimeBetween('-5 years', '+1 month')->format('Y-m-d'),
            'age_rating' => $faker->randomElement(Movie::AGE_RATINGS),
        ]));

        $halls = collect([['Hall 1', '2D', 10, 12], ['Hall 2', '3D', 8, 10], ['IMAX', 'IMAX', 14, 20], ['VIP Lounge', 'VIP', 4, 6], ['Hall 5', '2D', 9, 14]])
            ->map(fn (array $h) => Hall::create(['name' => $h[0], 'type' => $h[1], 'rows_count' => $h[2], 'seats_per_row' => $h[3]]));

        $screenings = collect(range(1, 60))->map(fn () => Screening::create([
            'movie_id' => $movies->random()->id,
            'hall_id' => $halls->random()->id,
            'starts_at' => today()->addDays($faker->numberBetween(-3, 14))
                ->setTime($faker->randomElement([10, 12, 14, 16, 18, 20, 22]), $faker->randomElement([0, 30])),
            'price' => $faker->randomElement([150, 180, 200, 250, 320, 450]),
            'language' => $faker->randomElement(self::LANGUAGES),
        ]))->each(fn (Screening $s) => $s->load('hall'));

        $customers = collect(range(1, 50))->map(fn () => Customer::create([
            'first_name' => $faker->firstName(),
            'last_name' => $faker->lastName(),
            'email' => $faker->unique()->safeEmail(),
            'phone' => $faker->optional(0.8)->numerify('+38050#######'),
            'birth_date' => $faker->dateTimeBetween('-60 years', '-14 years')->format('Y-m-d'),
        ]));

        $takenSeats = [];
        for ($i = 0; $i < 150; $i++) {
            $screening = $screenings->random();
            do {
                $row = $faker->numberBetween(1, $screening->hall->rows_count);
                $seat = $faker->numberBetween(1, $screening->hall->seats_per_row);
                $key = "{$screening->id}:$row:$seat";
            } while (isset($takenSeats[$key]));
            $takenSeats[$key] = true;

            Ticket::create([
                'screening_id' => $screening->id,
                'customer_id' => $customers->random()->id,
                'seat_row' => $row,
                'seat_number' => $seat,
                'price' => $screening->price,
                'status' => $faker->randomElement(['reserved', 'paid', 'paid', 'paid', 'cancelled']),
            ]);
        }

        // Demo accounts, one per role; the client owns customer #1
        User::create(['name' => 'Admin', 'email' => 'admin@cinema.test', 'password' => 'admin123', 'role' => User::ROLE_ADMIN]);
        User::create(['name' => 'Manager', 'email' => 'manager@cinema.test', 'password' => 'manager123', 'role' => User::ROLE_MANAGER]);
        $client = $customers->first();
        User::create([
            'name' => "{$client->first_name} {$client->last_name}",
            'email' => 'client@cinema.test',
            'password' => 'client123',
            'role' => User::ROLE_CLIENT,
            'customer_id' => $client->id,
        ]);
    }
}
