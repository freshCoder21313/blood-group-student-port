@props(['status'])

@php
    $baseClasses = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full';
    
    $variants = [
        'approved' => 'bg-emerald-100 text-emerald-800',
        'verified' => 'bg-emerald-100 text-emerald-800',
        'completed' => 'bg-emerald-100 text-emerald-800',
        
        'pending' => 'bg-amber-100 text-amber-800',
        'pending_payment' => 'bg-amber-100 text-amber-800',
        'pending_approval' => 'bg-primary-100 text-primary-800',
        'pending_verification' => 'bg-amber-100 text-amber-800',
        
        'draft' => 'bg-gray-100 text-gray-800',
        'request_info' => 'bg-orange-100 text-orange-800',
        
        'rejected' => 'bg-rose-100 text-rose-800',
        'failed' => 'bg-rose-100 text-rose-800',
        
        'student' => 'bg-primary-100 text-primary-800',
        'submitted' => 'bg-primary-100 text-primary-800',
    ];
    
    $labels = [
        'request_info' => 'More Info Requested',
        'pending_approval' => 'Pending Approval',
        'pending_payment' => 'Pending Payment',
    ];

    $classes = $baseClasses . ' ' . ($variants[$status] ?? 'bg-gray-100 text-gray-800');
    $label = $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $label }}
</span>