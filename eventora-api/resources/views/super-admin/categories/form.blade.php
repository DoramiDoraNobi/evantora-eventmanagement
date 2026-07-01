<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ isset($category) ? __('Edit Category: ') . $category->name : __('Create Category') }}
            </h2>
            <a href="{{ route('super-admin.categories.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">&larr; Back to Categories</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <h3 class="font-semibold text-red-800 text-sm mb-2">Please fix the following errors:</h3>
                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="post"
                          action="{{ isset($category) ? route('super-admin.categories.update', $category) : route('super-admin.categories.store') }}"
                          class="space-y-6">
                        @csrf
                        @if(isset($category))
                            @method('PATCH')
                        @endif

                        <div>
                            <x-input-label for="name" :value="__('Category Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $category->name ?? '')" required autofocus placeholder="e.g. Music, Technology, Sports" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="icon" :value="__('Icon (Emoji)')" />
                                <x-text-input id="icon" name="icon" type="text" class="mt-1 block w-full" :value="old('icon', $category->icon ?? '')" placeholder="e.g. 🎵, 💻, ⚽" />
                                <p class="text-xs text-gray-500 mt-1">Paste an emoji or leave blank</p>
                                <x-input-error class="mt-2" :messages="$errors->get('icon')" />
                            </div>
                            <div>
                                <x-input-label for="color" :value="__('Color')" />
                                <div class="flex items-center gap-2 mt-1">
                                    <input id="color" name="color" type="color" class="h-10 w-14 rounded border border-gray-300 cursor-pointer" value="{{ old('color', $category->color ?? '#6366f1') }}" />
                                    <span class="text-xs text-gray-500">Badge color</span>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('color')" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="sort_order" :value="__('Sort Order')" />
                                <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 block w-full" :value="old('sort_order', $category->sort_order ?? 0)" />
                                <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
                                <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                            </div>
                            <div>
                                <x-input-label for="is_active" :value="__('Status')" />
                                <select id="is_active" name="is_active" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="1" {{ old('is_active', $category->is_active ?? true) ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !old('is_active', $category->is_active ?? true) ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t">
                            <x-primary-button>{{ isset($category) ? __('Update Category') : __('Create Category') }}</x-primary-button>
                            <a href="{{ route('super-admin.categories.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
