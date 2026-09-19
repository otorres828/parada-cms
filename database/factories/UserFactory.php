<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\UserTypeGender;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type_audience = fake()->randomElement(['1', '2', '3']);
        $media_id = null;
        if($type_audience == '2'){
            $media = Media::inRandomOrder()->first();
            $media_id = $media? $media->id : null;
        }

        return [
            'name' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'date_birth' => fake()->date(),
            'sex' => fake()->randomElement(['1', '2']),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'token' => fake()->optional()->sha1(),
            'last_login_at' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
            'impact' => fake()->randomElement(['1', '2', '3']),
            'audience_type' => $type_audience,
            'media_id'=> $media_id,
            'status' => fake()->randomElement([1,2,3]),
        ];
    }
}
