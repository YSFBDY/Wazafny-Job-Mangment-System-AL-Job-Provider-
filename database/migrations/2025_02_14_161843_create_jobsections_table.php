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
        Schema::create('jobsections', function (Blueprint $table) {
            $table->id('section_id');
            $table->string('section_name');
            $table->text('section_description');

            $table->unsignedBigInteger('job_id');
            $table->foreign("job_id")->references('job_id')->on('jobposts')->cascadeOnDelete()->cascadeOnUpdate();

            $table->timestamps();       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobsections');
    }
};
