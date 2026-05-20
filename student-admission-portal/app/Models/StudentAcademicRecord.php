<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAcademicRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_code',
        'grades',
        'schedule',
        'fees',
    ];

    /**
     * Configure attribute casting for JSON fields.
     */
    protected function casts(): array
    {
        return [
            'grades' => 'array',
            'schedule' => 'array',
            'fees' => 'array',
        ];
    }

    /**
     * Get the student associated with these academic records.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_code', 'student_code');
    }
}
