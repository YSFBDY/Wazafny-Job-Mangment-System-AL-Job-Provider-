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
        Schema::create('companies', function (Blueprint $table) {
            $table->id('company_id');

            $table->unsignedBigInteger('user_id');
            $table->foreign("user_id")->references('user_id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            
            $table->string('company_name');
            $table->string('company_email')->unique()->nullable();
            $table->string('company_industry')->nullable();
            $table->year('company_founded')->nullable();
            $table->string('company_size')->nullable();
            $table->string('company_heads')->nullable();
            $table->string('company_website_link')->nullable();
            $table->string('company_country')->nullable();
            $table->string('company_city')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies_');
    }
};
