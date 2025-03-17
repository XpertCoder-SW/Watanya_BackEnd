<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';
    protected $fillable = ['name', 'phoneNumber', 'email', 'password', 'code', 'level', 'specialization', 'gpa', 'academic_year'];

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
    public function gradesByAcademicYear($academicYear)
    {
        return $this->grades()->where('academic_year', $academicYear)->get();
    }
}
