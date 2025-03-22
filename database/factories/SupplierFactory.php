<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use App\Models\BankAccount;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'address_id' => Address::factory(),
            'bank_account_id' => BankAccount::factory(),
            'name' => $this->faker->company(),
            'registration_number' => $this->faker->numerify(Str::repeat('#', 10)),
            'vat_number' => $this->faker->regexify('[A-Z]{2}[0-9]{10}'),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
