<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dni' => $this->faker->numerify('########'),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'full_name' => $this->faker->name,
            'gender' => $this->faker->randomElement(['male', 'female']),
            'phone_number' => $this->faker->numerify('#########'),
            'email' => $this->faker->unique()->safeEmail,
            'date_of_birth' => $this->faker->date(),
            'age' => $this->faker->numberBetween(18, 60),
            'nationality' => $this->faker->country,
            'family_phone_number' => $this->faker->numerify('#########'),
            'linkedin' => 'https://www.linkedin.com/in/' . $this->faker->userName,
            'password' => bcrypt('password'), // password
            'role' => 'user',
            'remember_token' => \Illuminate\Support\Str::random(10),
        ];
    }
}
