<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => fn () => User::factory(),
            'bank_name' => $this->faker->name(),
            'account_number' => $this->faker->numerify(Str::repeat('#', 10)),
            'bank_code' => $this->faker->numerify(Str::repeat('#', 4)),
            'iban' => $this->faker->iban(),
            'swift' => $this->faker->swiftBicNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
