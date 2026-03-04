<?php

declare(strict_types=1);

use App\Enums\RequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();

            // По ТЗ: обязательные
            $table->string('client_name');
            $table->string('phone', 32);
            $table->string('address');
            $table->text('problem_text');

            $table->enum('status', RequestStatus::values())
                ->default(RequestStatus::New->value);

            $table->foreignId('assigned_master_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('status');
            $table->index(['assigned_master_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};