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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('creditHours');
            $table->string('code')->unique();
            $table->enum('specialization', ['CS', 'IT']);
            $table->enum('level', ['One', 'Two', 'Three', 'Four']);
            $table->enum('semester', ['One', 'Two']);
            // $table->string('totalGradeChar')->nullable();
            $table->decimal('totalGrade', 5, 2)->nullable();
            $table->decimal('yearsWorkGrade', 5, 2)->nullable();
            $table->decimal('midtermGrade', 5, 2)->nullable();
            $table->decimal('finalGrade', 5, 2)->nullable();
            $table->decimal('practicalGrade', 5, 2)->nullable();
            // $table->enum('gradeStatus', ['ff*', 'i*', 'i', 'others'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
