<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'from_status',
        'to_status',
        'changed_by',
        'comment',
        'source',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
