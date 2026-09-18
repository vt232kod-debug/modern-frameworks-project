<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('seat_row');
            $table->unsignedTinyInteger('seat_number');
            $table->decimal('price', 8, 2);
            $table->string('status', 20)->default('reserved');
            $table->dateTime('purchased_at');
            $table->timestamps();

            $table->unique(['screening_id', 'seat_row', 'seat_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
