<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {

            $table->decimal('original_amount', 10, 2)->nullable()->after('people_count');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('original_amount');
            $table->foreignId('coupon_id')
                ->nullable()
                ->after('discount_amount')
                ->constrained()
                ->nullOnDelete();
            $table->string('coupon_code')
                ->nullable()
                ->after('coupon_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            //
        });
    }
};
