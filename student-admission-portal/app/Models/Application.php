<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'program_id',
        'block_id',
        'application_number',
        'status',
        'current_step',
        'total_steps',
        'admin_notes',
        'rejection_reason',
        'submitted_at',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function block()
    {
        return $this->belongsTo(AcademicBlock::class);
    }

    // Alias for block for easier understanding
    public function academicBlock()
    {
        return $this->belongsTo(AcademicBlock::class, 'block_id');
    }

    public function steps()
    {
        return $this->hasMany(ApplicationStep::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(StatusHistory::class);
    }
}
