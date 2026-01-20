<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'step_number',
        'step_name',
        'data',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
