<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('city_id')->nullable()->after('city');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_city_id')->nullable()->after('shipping_city');
            $table->decimal('shipping_cost', 12, 2)->default(0)->after('total_amount');
            $table->string('courier')->nullable()->after('shipping_cost');
            $table->string('courier_service')->nullable()->after('courier');
        });
    }

    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn('city_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_city_id', 'shipping_cost', 'courier', 'courier_service']);
        });
    }
};
