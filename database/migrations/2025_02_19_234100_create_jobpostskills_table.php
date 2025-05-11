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
        Schema::create('jobpostskills', function (Blueprint $table) {
            $table->unsignedBigInteger('job_id');
            $table->foreign("job_id")->references('job_id')->on('jobposts')->cascadeOnDelete()->cascadeOnUpdate();
            
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
        Schema::dropIfExists('jobpostskills');
    }
};
