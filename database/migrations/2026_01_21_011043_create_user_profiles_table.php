<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table): void {
            $table->ulid('id')->primary();

            $table->foreignUlid('user_id')
                ->constrained(table: 'users', column: 'id')
                ->cascadeOnDelete();

            $table->string('full_name')
                ->storedAs("
                    COALESCE(title || ' ', '') ||
                    first_name ||
                    ' ' ||
                    COALESCE(middle_name || ' ', '') ||
                    last_name ||
                    COALESCE(' ' || suffix, '')
                ")
                ->index();

            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->string('sex');
            $table->string('mobile_number')->nullable();

            $table->json('preferences')->nullable();

            $table->timestamps();
        });
    }
};
