<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                    // COUPON123
            $table->enum('discount_type', ['fixed', 'percent']); // fixed=500, percent=20%
            $table->decimal('discount_value', 10, 2);           // 500.00 or 20.00
            $table->decimal('min_amount', 10, 2)->nullable()->default(0);   // Min booking amount
            $table->integer('usage_limit')->nullable();         // 20 uses total
            $table->integer('used_count')->default(0);          // 5 used already
            $table->date('start_date');                         // 2026-02-15
            $table->date('end_date');                           // 2026-03-15
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Index for fast lookup
            $table->index('code');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons');
    }
};
