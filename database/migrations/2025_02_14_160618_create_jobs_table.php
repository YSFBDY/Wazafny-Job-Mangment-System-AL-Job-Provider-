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
        Schema::create('jobposts', function (Blueprint $table) {
            $table->id('job_id');
            $table->string('job_title');
            $table->text('job_about');
            $table->enum('job_time', ['Full-time', 'Part-time']);
            $table->enum('job_type', ['On-site', 'Remote']);
            $table->enum('job_status', ['Active', 'Closed']);
            $table->string('job_country');
            $table->string('job_city');

            $table->unsignedBigInteger('company_id');
            $table->foreign("company_id")->references('company_id')->on('companies')->cascadeOnDelete()->cascadeOnUpdate();

            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
