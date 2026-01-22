<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'student_code',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'nationality',
        'national_id',
        'passport_number',
        'address',
        'city',
        'county',
        'postal_code',
        'profile_photo',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'national_id' => 'encrypted',
            'passport_number' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Student $student) {
            if ($student->isDirty('national_id')) {
                $student->national_id_index = $student->generateBlindIndex($student->national_id);
            }

            if ($student->isDirty('passport_number')) {
                $student->passport_number_index = $student->generateBlindIndex($student->passport_number);
            }
        });
    }

    public function generateBlindIndex(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return hash_hmac('sha256', $value, config('app.blind_index_key'));
    }

    public function scopeWhereNationalId($query, string $value)
    {
        return $query->where('national_id_index', $this->generateBlindIndex($value));
    }

    public function scopeWherePassportNumber($query, string $value)
    {
        return $query->where('passport_number_index', $this->generateBlindIndex($value));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function application()
    {
        return $this->hasOne(Application::class)->latestOfMany();
    }

    public function parentInfo()
    {
        return $this->hasOne(ParentInfo::class);
    }
}
