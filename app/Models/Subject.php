<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'creditHours', 'code', 'specialization', 'level', 'semester', 'totalGradeChar', 'totalGrade', 'yearsWorkGrade', 'midtermGrade', 'finalGrade', 'practicalGrade', 'gradeStatus'];

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_subject', 'subject_id', 'doctor_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
    public function students()
{
    return $this->belongsToMany(Student::class, 'student_subject', 'subject_id', 'student_id');
}
}
