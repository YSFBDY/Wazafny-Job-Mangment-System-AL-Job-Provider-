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
        Schema::create('applications', function (Blueprint $table) {
            $table->id('application_id');
            $table->enum('status', ['Pending', 'Accepted', 'Rejected']);
            $table->text('response')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('resume');
            $table->string('country');
            $table->string('city');
            $table->string('phone');

            $table->unsignedBigInteger('seeker_id');
            $table->foreign("seeker_id")->references('seeker_id')->on('jobseekers')->cascadeOnDelete()->cascadeOnUpdate();

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
        Schema::dropIfExists('applications');
    }
};
