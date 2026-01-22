@props(['disabled' => false, 'error' => false])

@php
    $classes = 'border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm w-full';
    
    if ($error) {
        $classes = 'border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm w-full';
    }
@endphp

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => $classes]) !!}>