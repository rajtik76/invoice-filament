<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => fn () => User::factory(),
            'address_id' => fn (array $attributes) => Address::factory()->create(['user_id' => $attributes['user_id']]),
            'name' => $this->faker->company(),
            'registration_number' => $this->faker->optional(0.2)->numerify(Str::repeat('#', 10)),
            'vat_number' => $this->faker->regexify('[A-Z]{2}[0-9]{10}'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
