<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wash_transactions', function (Blueprint $table): void {
            $table->index(['created_at', 'status'], 'wash_transactions_created_status_index');
            $table->index(['washer_id', 'created_at', 'status'], 'wash_transactions_washer_created_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('wash_transactions', function (Blueprint $table): void {
            $table->dropIndex('wash_transactions_created_status_index');
            $table->dropIndex('wash_transactions_washer_created_status_index');
        });
    }
};
