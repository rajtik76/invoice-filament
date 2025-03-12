<?php

use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignIdFor(Contract::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('number');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->date('issue_date');
            $table->date('due_date');
            $table->jsonb('content');
            $table->decimal('total_amount')->default(0)->unsigned();

            $table->unique(['user_id', 'contract_id', 'number']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
