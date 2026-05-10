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
        Schema::create('listings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('location');
            $table->string('category');
            $table->string('time_commitment')->nullable();
            $table->integer('spots')->default(1);
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->boolean('is_new')->default(true);
            $table->boolean('is_online')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
