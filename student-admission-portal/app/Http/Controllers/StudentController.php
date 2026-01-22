<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function grades()
    {
        abort(404, 'Student Grades Coming Soon');
    }

    public function schedule()
    {
        abort(404, 'Class Schedule Coming Soon');
    }

    public function fees()
    {
        abort(404, 'Fee Statement Coming Soon');
    }
}
