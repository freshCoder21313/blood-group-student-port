<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Site Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                @php
                    $groupLabels = [
                        'general' => ['title' => 'General Settings', 'icon' => '⚙️', 'desc' => 'Basic information about your institution.'],
                        'contact' => ['title' => 'Contact Information', 'icon' => '📞', 'desc' => 'Contact details displayed on the public site and contact page.'],
                        'footer'  => ['title' => 'Footer Settings', 'icon' => '🦶', 'desc' => 'Customize the footer content. Use {year} and {app_name} as placeholders.'],
                    ];

                    $fieldLabels = [
                        'school_name'       => 'School / Institution Name',
                        'school_tagline'    => 'Tagline / Motto',
                        'school_email'      => 'Email Address',
                        'school_phone'      => 'Phone Number',
                        'school_address'    => 'Physical Address',
                        'school_website'    => 'Website URL',
                        'footer_copyright'  => 'Copyright Text',
                        'footer_description'=> 'Footer Description',
                    ];
                @endphp

                @foreach($groupLabels as $groupKey => $groupMeta)
                    <x-ui.card class="mb-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ $groupMeta['icon'] }} {{ $groupMeta['title'] }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $groupMeta['desc'] }}</p>
                        </div>

                        <div class="space-y-4">
                            @if(isset($settings[$groupKey]))
                                @foreach($settings[$groupKey] as $setting)
                                    <div>
                                        <x-ui.input-label :for="'setting_' . $setting->key"
                                            :value="$fieldLabels[$setting->key] ?? ucwords(str_replace('_', ' ', $setting->key))" />

                                        @if(in_array($setting->key, ['footer_description', 'school_address']))
                                            <textarea
                                                id="setting_{{ $setting->key }}"
                                                name="settings[{{ $setting->key }}]"
                                                rows="3"
                                                class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm w-full block mt-1"
                                            >{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                        @else
                                            <x-ui.input
                                                id="setting_{{ $setting->key }}"
                                                name="settings[{{ $setting->key }}]"
                                                type="text"
                                                class="mt-1 block w-full"
                                                :value="old('settings.' . $setting->key, $setting->value)" />
                                        @endif

                                        <x-ui.input-error :messages="$errors->get('settings.' . $setting->key)" class="mt-2" />
                                    </div>
                                @endforeach
                            @else
                                <p class="text-sm text-gray-400 italic">No settings in this group yet.</p>
                            @endif
                        </div>
                    </x-ui.card>
                @endforeach

                <div class="flex justify-end">
                    <x-ui.button type="submit" variant="primary">
                        {{ __('Save All Settings') }}
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
