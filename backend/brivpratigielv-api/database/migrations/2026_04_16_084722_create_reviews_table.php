<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('listing_id')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->uuid('reviewed_user_id')->nullable();
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->string('review_type')->default('listing');
            $table->timestamps();

            $table->foreign('listing_id')->references('id')->on('listings')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
