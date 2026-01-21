<?php

return [
    'steps' => [
        1 => ['name' => 'personal_info', 'label' => 'Personal Information'],
        2 => ['name' => 'parent_info', 'label' => 'Parent/Guardian Information'],
        3 => ['name' => 'program_selection', 'label' => 'Program Selection'],
        4 => ['name' => 'documents', 'label' => 'Document Upload'],
        5 => ['name' => 'payment', 'label' => 'Payment'],
    ],
    'payment' => [
        'amount' => env('APPLICATION_FEE', 1000),
    ],
];
