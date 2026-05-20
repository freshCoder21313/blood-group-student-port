<footer class="bg-white border-t border-gray-200 py-8">
    <x-ui.container>
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-center md:text-left">
                <p class="text-sm text-gray-500">
                    @php
                        $copyright = \App\Models\SiteSetting::get('footer_copyright', '© {year} {app_name}. All rights reserved.');
                        $copyright = str_replace('{year}', date('Y'), $copyright);
                        $copyright = str_replace('{app_name}', config('app.name'), $copyright);
                    @endphp
                    {{ $copyright }}
                </p>
                @php
                    $description = \App\Models\SiteSetting::get('footer_description');
                @endphp
                @if($description)
                    <p class="text-xs text-gray-400 mt-1">{{ $description }}</p>
                @endif
            </div>
            <div class="flex gap-6 text-sm text-gray-500">
                <a href="{{ route('page.show', 'terms') }}" class="hover:text-gray-900 transition-colors">{{ __('Terms') }}</a>
                <a href="{{ route('page.show', 'privacy') }}" class="hover:text-gray-900 transition-colors">{{ __('Privacy') }}</a>
                <a href="{{ route('page.show', 'contact') }}" class="hover:text-gray-900 transition-colors">{{ __('Contact') }}</a>
            </div>
        </div>
    </x-ui.container>
</footer>
