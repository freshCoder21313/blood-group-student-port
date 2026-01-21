<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        $user->loadMissing('student');
        
        if (! $user->student) {
            return false;
        }

        return $document->application->student_id === $user->student->id;
    }

    public function delete(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }
}
