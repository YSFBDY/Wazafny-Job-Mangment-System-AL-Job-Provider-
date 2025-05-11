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
        Schema::create('seekerskills', function (Blueprint $table) {
            $table->unsignedBigInteger('seeker_id');
            $table->foreign("seeker_id")->references('seeker_id')->on('jobseekers')->cascadeOnDelete()->cascadeOnUpdate();

            $table->unsignedBigInteger('skill_id');
            $table->foreign("skill_id")->references('skill_id')->on('skills')->cascadeOnDelete()->cascadeOnUpdate();
           
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seekerskills');
    }
};
