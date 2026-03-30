<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rental_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('party_id');
            $table->unsignedBigInteger('house_id');
            $table->decimal('security_money', 60, 2)->default(0.00);
            $table->decimal('remaining_security_money', 60, 2)->default(0.00);
            $table->decimal('refund_security_money', 60, 2)->default(0.00);
            $table->decimal('monthly_rent', 60, 2);
            $table->decimal('auto_adjustment', 60, 2)->default(0.00);

            $table->unsignedBigInteger('payment_channel_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('rent_start_date');
            $table->string('rent_end_date');
            $table->enum('status', ['active', 'inactive'])
                ->default('active');
            $table->timestamps();

            $table->foreign('house_id')->references('id')->on('rental_houses')->onDelete('cascade');
            $table->foreign('party_id')->references('id')->on('rental_parties')->onDelete('cascade');
            $table->foreign('payment_channel_id')->references('id')->on('payment_channel_details')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('account_numbers')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_mappings');
    }
};
