<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('working_hours', function (Blueprint $table) {
            $table->enum('status', ['open', 'off'])->default('open')->after('day_type');
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('working_hours', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->time('start_time')->change();
            $table->time('end_time')->change();
        });
    }
};
