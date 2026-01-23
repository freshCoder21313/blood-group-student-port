<footer class="bg-white border-t border-gray-200 py-8">
    <x-ui.container>
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-gray-500">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <div class="flex gap-6 text-sm text-gray-500">
                <a href="#" class="hover:text-gray-900 transition-colors">{{ __('Terms') }}</a>
                <a href="#" class="hover:text-gray-900 transition-colors">{{ __('Privacy') }}</a>
                <a href="#" class="hover:text-gray-900 transition-colors">{{ __('Contact') }}</a>
            </div>
        </div>
    </x-ui.container>
</footer>
