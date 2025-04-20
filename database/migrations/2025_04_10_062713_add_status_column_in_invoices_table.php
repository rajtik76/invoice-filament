<?php
declare(strict_types=1);

use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status')->default(InvoiceStatusEnum::Draft)->after('number');
        });

        Invoice::query()->update(['status' => InvoiceStatusEnum::Issued]);
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
