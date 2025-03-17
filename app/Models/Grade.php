<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = ['student_id', 'subject_id', 'academic_year', 'totalGradeChar', 'totalGrade', 'yearsWorkGrade', 'midtermGrade', 'finalGrade', 'practicalGrade', 'gradeStatus'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
