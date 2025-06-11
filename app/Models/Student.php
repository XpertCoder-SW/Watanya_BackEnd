<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'code',
        'name',
        'email',
        'phoneNumber',
        'level',
        'specialization',
        'academic_year',
        'gpa',
        'password',
    ];

    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
    ];
    
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
    public function gradesByAcademicYear($academicYear)
    {
        return $this->grades()->where('academic_year', $academicYear)->get();
    }

    public function subjects() {
        return $this->belongsToMany(Subject::class, 'student_subject')
                   ->withPivot('academic_year');
    }

}
