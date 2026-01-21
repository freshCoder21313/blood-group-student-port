<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'type',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
