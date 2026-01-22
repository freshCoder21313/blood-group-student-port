@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'submit',
    'disabled' => false,
    'href' => null
])

@php
    $baseClasses = 'inline-flex items-center justify-center border border-transparent font-semibold rounded-md uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150';
    
    $variants = [
        'primary' => 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500',
        'secondary' => 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 focus:ring-primary-500 shadow-sm',
        'danger' => 'bg-red-600 text-white hover:bg-red-500 focus:ring-red-500',
        'success' => 'bg-secondary-600 text-white hover:bg-secondary-700 focus:ring-secondary-500',
        'ghost' => 'bg-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:ring-gray-500',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-xs',
        'lg' => 'px-6 py-3 text-sm',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']) . ($disabled ? ' opacity-50 cursor-not-allowed pointer-events-none' : '');
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $disabled ? 'disabled' : '' }} type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif