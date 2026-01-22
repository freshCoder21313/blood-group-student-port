@props(['name', 'label', 'accept' => 'image/*,.pdf', 'value' => null, 'deleteUrl' => null, 'initialIsImage' => true, 'disabled' => false])

<div x-data="imageUploader('{{ $value }}', '{{ $deleteUrl }}', {{ $initialIsImage ? 'true' : 'false' }}, {{ $disabled ? 'true' : 'false' }})"
    class="w-full">
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>

    <!-- Drop Zone -->
    <div x-show="!previewUrl && !disabled" @dragover.prevent="dragover = true" @dragleave.prevent="dragover = false"
        @drop.prevent="handleDrop($event)"
        class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors duration-200"
        :class="dragover ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-gray-400'"
        @click="$refs.fileInput.click()">
        <input type="file" name="{{ $name }}" x-ref="fileInput" class="hidden" accept="{{ $accept }}"
            @change="handleFileSelect($event)">

        <div class="space-y-1">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path
                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="text-sm text-gray-600">
                <span class="font-medium text-primary-600 hover:text-primary-500">Upload a file</span>
                or drag and drop
            </div>
            <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
        </div>
    </div>

    <!-- Preview -->
    <div x-show="previewUrl" class="relative mt-2 border rounded-lg p-2 flex items-center justify-between"
        style="display: none;">
        <div class="flex items-center space-x-3 overflow-hidden">
            <!-- Image Preview -->
            <template x-if="isImage">
                <img :src="previewUrl" class="h-16 w-16 object-cover rounded" />
            </template>
            <!-- PDF/File Icon -->
            <template x-if="!isImage">
                <div class="h-16 w-16 bg-gray-100 rounded flex items-center justify-center text-gray-500">
                    <span class="text-xs font-bold uppercase" x-text="fileExtension">DOC</span>
                </div>
            </template>

            <div class="truncate">
                <p class="text-sm font-medium text-gray-900 truncate" x-text="fileName || 'Existing File'"></p>
                <p class="text-xs text-gray-500" x-text="fileSize"></p>
            </div>
        </div>

        <button type="button" x-show="!disabled" @click="removeFile"
            class="ml-2 p-1 text-gray-400 hover:text-red-500 rounded-full hover:bg-gray-100">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Error Message -->
    <p x-show="error" x-text="error" class="mt-2 text-sm text-red-600" style="display: none;"></p>
</div>