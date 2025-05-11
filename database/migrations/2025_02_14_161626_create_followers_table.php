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
        Schema::create('followers', function (Blueprint $table) {
            $table->unsignedBigInteger('seeker_id');
            $table->foreign("seeker_id")->references('seeker_id')->on('jobseekers')->cascadeOnDelete()->cascadeOnUpdate();
            
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
        Schema::dropIfExists('followers');
    }
};
