<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phoneNumber')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('code')->unique();
            $table->enum('level', ['One', 'Two', 'Three', 'Four']);
            $table->enum('specialization', ['CS', 'IT']);
            $table->string('Academic-Year');
            $table->decimal('gpa', 3, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
