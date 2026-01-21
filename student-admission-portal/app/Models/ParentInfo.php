<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentInfo extends Model
{
    use HasFactory;

    protected $table = 'parent_info';

    protected $fillable = [
        'student_id',
        'relationship',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
