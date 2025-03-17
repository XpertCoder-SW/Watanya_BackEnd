<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'creditHours', 'code', 'specialization', 'level', 'semester', 'totalGradeChar', 'totalGrade', 'yearsWorkGrade', 'midtermGrade', 'finalGrade', 'practicalGrade', 'gradeStatus'];

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_subject');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}
