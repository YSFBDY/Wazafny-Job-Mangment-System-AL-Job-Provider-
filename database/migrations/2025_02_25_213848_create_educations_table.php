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
        Schema::create('educations', function (Blueprint $table) {
            $table->id("education_id");
            $table->string("university");
            $table->string("college");
            $table->string("start_date");
            $table->string("end_date");

            $table->unsignedBigInteger('seeker_id');
            $table->foreign("seeker_id")->references('seeker_id')->on('jobseekers')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educations');
    }
};
