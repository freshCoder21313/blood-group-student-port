@props(['title' => '', 'description' => '', 'icon' => ''])

<div {{ $attributes->merge(['class' => 'bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition-shadow duration-200 h-full flex flex-col']) }}>
    <div class="flex items-center mb-4">
        @if ($icon)
            <div class="mr-3 text-primary-500">
                {{ $icon }}
            </div>
        @endif
        <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
    </div>
    
    @if ($description)
        <p class="text-sm text-gray-500 mb-4 flex-grow">{{ $description }}</p>
    @endif

    <div class="mt-auto pt-4">
        {{ $slot }}
    </div>
</div>
