@props(['status'])

@php
    $colors = [
        'draft' => 'bg-amber-100 text-amber-800',
        'submitted' => 'bg-primary-100 text-primary-800',
        'pending_payment' => 'bg-yellow-100 text-yellow-800',
        'pending_approval' => 'bg-primary-100 text-primary-800',
        'request_info' => 'bg-orange-100 text-orange-800',
        'approved' => 'bg-emerald-100 text-emerald-800',
        'rejected' => 'bg-rose-100 text-rose-800',
    ];
    
    $labels = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'pending_payment' => 'Pending Payment',
        'pending_approval' => 'Pending Approval',
        'request_info' => 'More Info Requested',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];
    
    $color = $colors[$status] ?? 'bg-gray-100 text-gray-800';
    $label = $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
@endphp

<span {{ $attributes->merge(['class' => "px-2 inline-flex text-xs leading-5 font-semibold rounded-full {$color}"]) }}>
    {{ $label }}
</span>
