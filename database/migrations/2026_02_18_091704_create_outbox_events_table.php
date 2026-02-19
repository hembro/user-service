<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbox_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('event_type');
            $table->jsonb('payload');
            $table->string('status')->index();
            $table->text('error')->nullable();
            $table->timestamps();

            // Index for the worker to find pending jobs quickly
            $table->index(['status', 'created_at']);
        });
    }
};
