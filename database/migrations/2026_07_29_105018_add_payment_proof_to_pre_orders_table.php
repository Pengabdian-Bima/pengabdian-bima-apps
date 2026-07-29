<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pre_orders', function (Blueprint $table) {
            // Payment proof (uploaded by user after processing)
            $table->string('payment_proof')->nullable()->after('payment_method');
            $table->string('payment_sender_name')->nullable()->after('payment_proof');
            $table->string('payment_sender_bank')->nullable()->after('payment_sender_name');
            $table->unsignedBigInteger('payment_amount')->nullable()->after('payment_sender_bank');
            $table->date('payment_date')->nullable()->after('payment_amount');
        });
    }

    public function down(): void
    {
        Schema::table('pre_orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_proof',
                'payment_sender_name',
                'payment_sender_bank',
                'payment_amount',
                'payment_date',
            ]);
        });
    }
};
