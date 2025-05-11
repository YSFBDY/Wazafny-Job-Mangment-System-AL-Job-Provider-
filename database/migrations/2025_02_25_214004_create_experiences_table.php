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
        Schema::create('experiences', function (Blueprint $table) {
            $table->id("experience_id");
            $table->string("company");
            $table->string("job_title");
            $table->enum('job_time', ['Full-time', 'Part-time']);
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
        Schema::dropIfExists('experiences');
    }
};
