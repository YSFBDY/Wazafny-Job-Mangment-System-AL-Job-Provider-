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
        Schema::create('answers', function (Blueprint $table) {
            $table->id("answer_id");
            $table->text('answer');

            $table->unsignedBigInteger('question_id');
            $table->foreign("question_id")->references('question_id')->on('questions')->cascadeOnDelete()->cascadeOnUpdate();
            
            $table->unsignedBigInteger('application_id');
            $table->foreign("application_id")->references('application_id')->on('applications')->cascadeOnDelete()->cascadeOnUpdate();
            
            $table->unique(['application_id', 'question_id']); // Prevent duplicate answers for the same question in one application

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
