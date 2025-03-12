<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    public function run(): void
    {
        foreach (User::all() as $user) {
            // each user have 3 contracts
            Contract::factory(3)->create(['user_id' => $user->id]);
        }
    }
}
