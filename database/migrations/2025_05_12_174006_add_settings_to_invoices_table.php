<?php

declare(strict_types=1);

use App\Casts\AsContractSettingsCast;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->json('settings')
                ->default(json_encode(AsContractSettingsCast::getDefaults()))
                ->after('status');
        });
    }
};
