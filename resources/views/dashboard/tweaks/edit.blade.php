<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Tweak') }}: {{ $tweak->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('dashboard.tweaks.show', $tweak) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition duration-150 ease-in-out">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View Details
                </a>
                <a href="{{ route('dashboard.tweaks.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition duration-150 ease-in-out">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Edit Form -->
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Edit Tweak Information
                    </h3>
                </div>

                <form action="{{ route('dashboard.tweaks.update', $tweak) }}" method="POST"
                    enctype="multipart/form-data" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Current Info Alert -->
                    <div
                        class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-sm text-blue-800 dark:text-blue-200 font-medium">
                                    Package: <span class="font-mono">{{ $tweak->package }}</span> |
                                    Current Version: <span class="font-semibold">{{ $tweak->version }}</span>
                                </p>
                                <p class="text-xs text-blue-600 dark:text-blue-300 mt-1">
                                    You can update the tweak by uploading a new .deb file or manually editing the fields
                                    below.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Upload New .deb File (Optional) -->
                    <div
                        class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 hover:border-blue-500 transition-colors">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Upload New .deb File (Optional)
                        </label>
                        <input type="file" name="file" accept=".deb" class="block w-full text-sm text-gray-500 dark:text-gray-400
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-lg file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100
                                dark:file:bg-blue-900 dark:file:text-blue-200
                                dark:hover:file:bg-blue-800
                                cursor-pointer">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Upload a new .deb file to update the tweak. This will extract and update all package
                            information.
                        </p>
                        @error('file')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Manual Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tweak Name *
                            </label>
                            <input type="text" name="name" value="{{ old('name', $tweak->name) }}" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Version -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Version *
                            </label>
                            <input type="text" name="version" value="{{ old('version', $tweak->version) }}" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Manual version change will be validated (cannot be older than current)
                            </p>
                            @error('version')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Author -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Author
                            </label>
                            <input type="text" name="author" value="{{ old('author', $tweak->author) }}"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('author')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Maintainer -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Maintainer
                            </label>
                            <input type="text" name="maintainer" value="{{ old('maintainer', $tweak->maintainer) }}"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('maintainer')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Section -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Section *
                            </label>
                            <input type="text" name="section" value="{{ old('section', $tweak->section) }}" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('section')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Homepage -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Homepage URL
                            </label>
                            <input type="url" name="homepage" value="{{ old('homepage', $tweak->homepage) }}"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('homepage')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Description
                        </label>
                        <textarea name="description" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">{{ old('description', $tweak->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Changelog -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Changelog (Optional)
                        </label>
                        <textarea name="changelog" rows="6" placeholder="Enter changelog entries for this update..."
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white font-mono text-sm">{{ old('changelog') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Add a changelog entry if you're updating the version. Each line will be a separate entry.
                        </p>
                        @error('changelog')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Force Changelog Checkbox -->
                    <div class="flex items-center">
                        <input type="checkbox" name="force_changelog" id="force_changelog" value="1"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                        <label for="force_changelog" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Create changelog entry even if version hasn't changed
                        </label>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150 ease-in-out">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Update Tweak
                        </button>
                        <a href="{{ route('dashboard.tweaks.show', $tweak) }}"
                            class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition duration-150 ease-in-out">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Changelog Management Section -->
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z"
                                clip-rule="evenodd" />
                        </svg>
                        Manage Changelogs
                        <span class="ml-auto text-sm font-normal text-gray-500 dark:text-gray-400">
                            {{ $tweak->changeLogs->count() }} {{ Str::plural('entry', $tweak->changeLogs->count()) }}
                        </span>
                    </h3>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Add New Changelog Form -->
                    <div
                        class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-6 border border-green-200 dark:border-green-800">
                        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Add New Changelog Entry
                        </h4>
                        <form action="{{ route('dashboard.tweaks.changelog.add', $tweak) }}" method="POST"
                            class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Version</label>
                                    <input type="text" name="version" value="{{ $tweak->version }}" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white text-sm">
                                </div>
                                <div class="md:col-span-3">
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Changelog</label>
                                    <textarea name="changelog" rows="2" required
                                        placeholder="What's new in this version..."
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white text-sm"></textarea>
                                </div>
                            </div>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150 ease-in-out text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Add Changelog
                            </button>
                        </form>
                    </div>

                    <!-- Existing Changelogs -->
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-gray-900 dark:text-white">Existing Changelogs</h4>
                        @forelse($tweak->changeLogs as $index => $changelog)
                            <div
                                class="bg-gray-50 dark:bg-gray-900 rounded-lg p-5 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center space-x-3">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                                {{ $index === 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                            Version {{ $changelog->version }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $changelog->created_at->format('M d, Y \a\t h:i A') }}
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button onclick="toggleEditForm('changelog-{{ $changelog->id }}')"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form
                                            action="{{ route('dashboard.tweaks.changelog.delete', [$tweak, $changelog]) }}"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this changelog entry?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Display Changelog -->
                                <div id="changelog-display-{{ $changelog->id }}">
                                    @if(is_array(json_decode($changelog->changelog, true)))
                                        <ul class="list-disc pl-6 space-y-1 text-gray-700 dark:text-gray-200 text-sm">
                                            @foreach(json_decode($changelog->changelog, true) as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="prose prose-sm dark:prose-invert max-w-none">
                                            {!! nl2br(e($changelog->changelog)) !!}
                                        </div>
                                    @endif
                                </div>

                                <!-- Edit Form (Hidden by default) -->
                                <div id="changelog-{{ $changelog->id }}" class="hidden mt-4">
                                    <form action="{{ route('dashboard.tweaks.changelog.update', [$tweak, $changelog]) }}"
                                        method="POST" class="space-y-3">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Version</label>
                                            <input type="text" name="version" value="{{ $changelog->version }}" required
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Changelog</label>
                                            <textarea name="changelog" rows="4" required
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm">{{ is_array(json_decode($changelog->changelog, true)) ? implode("\n", json_decode($changelog->changelog, true)) : $changelog->changelog }}</textarea>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition text-sm">
                                                Save Changes
                                            </button>
                                            <button type="button" onclick="toggleEditForm('changelog-{{ $changelog->id }}')"
                                                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition text-sm">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-600 dark:text-gray-400 text-center py-4">No changelog entries yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleEditForm(id) {
            const editForm = document.getElementById(id);
            const displayDiv = document.getElementById(id.replace('changelog-', 'changelog-display-'));

            if (editForm.classList.contains('hidden')) {
                editForm.classList.remove('hidden');
                displayDiv.classList.add('hidden');
            } else {
                editForm.classList.add('hidden');
                displayDiv.classList.remove('hidden');
            }
        }
    </script>
</x-app-layout>