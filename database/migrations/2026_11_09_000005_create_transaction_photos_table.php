<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wash_transaction_id')->constrained('wash_transactions')->onDelete('cascade');
            $table->enum('photo_type', ['before', 'after']);
            $table->string('photo_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_photos');
    }
};
