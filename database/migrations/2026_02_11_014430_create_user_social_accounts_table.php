<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_social_accounts', function (Blueprint $table) {
            $table->ulid()->primary();

            $table->foreignUlid('user_id')
                ->constrained(table: 'users', column: 'id')
                ->cascadeOnDelete();

            $table->string('provider_id');
            $table->string('provider_name');

            $table->timestamps();

            $table->unique(['provider_name', 'provider_id']);
            $table->unique(['user_id', 'provider_name']);
        });
    }
};
