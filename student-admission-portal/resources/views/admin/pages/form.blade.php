<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $page->exists ? __('Edit Page') : __('Create Page') }}
            </h2>
            <x-ui.button variant="secondary" href="{{ route('admin.pages.index') }}">
                ← Back to Pages
            </x-ui.button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <form action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" method="POST">
                @csrf
                @if($page->exists)
                    @method('PUT')
                @endif

                <x-ui.card class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Page Details</h3>

                    <div class="space-y-4">
                        {{-- Title --}}
                        <div>
                            <x-ui.input-label for="title" value="Page Title" />
                            <x-ui.input
                                id="title"
                                name="title"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('title', $page->title)"
                                required
                                placeholder="e.g. About Us" />
                            <x-ui.input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        {{-- Slug --}}
                        <div>
                            <x-ui.input-label for="slug" value="URL Slug" />
                            <div class="flex items-center mt-1">
                                <span class="text-sm text-gray-500 mr-2">/page/</span>
                                <x-ui.input
                                    id="slug"
                                    name="slug"
                                    type="text"
                                    class="block w-full"
                                    :value="old('slug', $page->slug)"
                                    required
                                    placeholder="about-us" />
                            </div>
                            <x-ui.input-error :messages="$errors->get('slug')" class="mt-2" />
                        </div>

                        {{-- Published Toggle --}}
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="is_published" value="0">
                            <input
                                type="checkbox"
                                id="is_published"
                                name="is_published"
                                value="1"
                                {{ old('is_published', $page->exists ? $page->is_published : true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                            <x-ui.input-label for="is_published" value="Published" class="!mb-0" />
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Page Content</h3>
                        <div class="flex gap-2 text-xs" id="editor-toolbar">
                            <button type="button" onclick="wrapSelection('b')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded font-bold" title="Bold">B</button>
                            <button type="button" onclick="wrapSelection('i')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded italic" title="Italic">I</button>
                            <button type="button" onclick="wrapSelection('u')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded underline" title="Underline">U</button>
                            <span class="border-l border-gray-300 mx-1"></span>
                            <button type="button" onclick="insertTag('h2')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-xs font-semibold" title="Heading 2">H2</button>
                            <button type="button" onclick="insertTag('h3')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-xs font-semibold" title="Heading 3">H3</button>
                            <button type="button" onclick="insertTag('p')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-xs" title="Paragraph">¶</button>
                            <span class="border-l border-gray-300 mx-1"></span>
                            <button type="button" onclick="insertList('ul')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-xs" title="Bullet List">• List</button>
                            <button type="button" onclick="insertList('ol')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-xs" title="Numbered List">1. List</button>
                            <button type="button" onclick="insertLink()" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-xs" title="Insert Link">🔗</button>
                            <span class="border-l border-gray-300 mx-1"></span>
                            <button type="button" onclick="togglePreview()" class="px-2 py-1 bg-primary-100 hover:bg-primary-200 text-primary-700 rounded text-xs font-medium" title="Preview">👁 Preview</button>
                        </div>
                    </div>

                    <div id="editor-container">
                        <textarea
                            id="content"
                            name="content"
                            rows="20"
                            class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm w-full block font-mono text-sm"
                            placeholder="Write your page content in HTML..."
                        >{{ old('content', $page->content) }}</textarea>
                    </div>

                    <div id="preview-container" class="hidden mt-4 p-6 bg-white border border-gray-200 rounded-lg prose prose-sm max-w-none">
                    </div>

                    <x-ui.input-error :messages="$errors->get('content')" class="mt-2" />
                </x-ui.card>

                <div class="flex justify-between items-center">
                    <a href="{{ route('admin.pages.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                    <x-ui.button type="submit" variant="primary">
                        {{ $page->exists ? __('Update Page') : __('Create Page') }}
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            const slugField = document.getElementById('slug');
            if (!slugField.dataset.edited) {
                slugField.value = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim();
            }
        });
        document.getElementById('slug').addEventListener('input', function() {
            this.dataset.edited = 'true';
        });

        // Simple HTML editor helpers
        function getTextarea() { return document.getElementById('content'); }

        function wrapSelection(tag) {
            const ta = getTextarea();
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            const selected = ta.value.substring(start, end);
            const replacement = `<${tag}>${selected}</${tag}>`;
            ta.value = ta.value.substring(0, start) + replacement + ta.value.substring(end);
            ta.focus();
            ta.setSelectionRange(start + tag.length + 2, start + tag.length + 2 + selected.length);
        }

        function insertTag(tag) {
            const ta = getTextarea();
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            const selected = ta.value.substring(start, end) || 'Your text here';
            const replacement = `<${tag}>${selected}</${tag}>\n`;
            ta.value = ta.value.substring(0, start) + replacement + ta.value.substring(end);
            ta.focus();
        }

        function insertList(type) {
            const ta = getTextarea();
            const pos = ta.selectionStart;
            const list = `<${type}>\n    <li>Item 1</li>\n    <li>Item 2</li>\n    <li>Item 3</li>\n</${type}>\n`;
            ta.value = ta.value.substring(0, pos) + list + ta.value.substring(pos);
            ta.focus();
        }

        function insertLink() {
            const url = prompt('Enter URL:', 'https://');
            if (url) {
                const ta = getTextarea();
                const start = ta.selectionStart;
                const end = ta.selectionEnd;
                const text = ta.value.substring(start, end) || 'Link text';
                const link = `<a href="${url}" class="text-primary-600 hover:underline">${text}</a>`;
                ta.value = ta.value.substring(0, start) + link + ta.value.substring(end);
                ta.focus();
            }
        }

        function togglePreview() {
            const editor = document.getElementById('editor-container');
            const preview = document.getElementById('preview-container');
            const content = document.getElementById('content').value;

            if (preview.classList.contains('hidden')) {
                preview.innerHTML = content || '<p class="text-gray-400">No content to preview.</p>';
                preview.classList.remove('hidden');
                editor.classList.add('hidden');
            } else {
                preview.classList.add('hidden');
                editor.classList.remove('hidden');
            }
        }
    </script>
</x-app-layout>
