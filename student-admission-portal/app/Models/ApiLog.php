<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'direction',
        'endpoint',
        'method',
        'request_body',
        'response_body',
        'status_code',
        'ip_address',
        'user_agent',
        'duration_ms',
        'api_key',
        'error_message',
    ];
}
