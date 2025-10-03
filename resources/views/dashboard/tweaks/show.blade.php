<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Tweak Details') }}
            </h2>
            <a href="{{ route('dashboard.tweaks.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition duration-150 ease-in-out">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Tweak Header Card -->
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-lg border border-gray-200 dark:border-gray-700">
                @if($tweak->header_url_full)
                    <div class="h-48 bg-cover bg-center relative"
                        style="background-image: url('{{ $tweak->header_url_full }}');">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    </div>
                @else
                    <div class="h-48 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 relative">
                        <div class="absolute inset-0 bg-black/20"></div>
                    </div>
                @endif

                <div class="p-8">
                    <div class="flex items-start space-x-6">
                        @if($tweak->icon_url_full)
                            <img class="h-24 w-24 rounded-2xl shadow-lg border-4 border-white dark:border-gray-700 -mt-16 relative z-10"
                                src="{{ $tweak->icon_url_full }}" alt="{{ $tweak->name }}">
                        @else
                            <div
                                class="h-24 w-24 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg border-4 border-white dark:border-gray-700 -mt-16 relative z-10">
                                <span class="text-white font-bold text-4xl">{{ substr($tweak->name, 0, 1) }}</span>
                            </div>
                        @endif

                        <div class="flex-1 pt-2">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $tweak->name }}</h1>

                            <div class="flex flex-wrap items-center gap-3 mb-4">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Version {{ $tweak->version }}
                                </span>

                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ $tweak->section }}
                                </span>

                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                                        <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                                    </svg>
                                    {{ $tweak->formatted_size }}
                                </span>
                            </div>

                            @if($tweak->description)
                                <p class="text-gray-600 dark:text-gray-400 text-base leading-relaxed mb-4">
                                    {{ $tweak->description }}
                                </p>
                            @endif

                            <div class="flex flex-wrap gap-2">
                                <a href="{{ $tweak->deb_file_url }}" download
                                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150 ease-in-out">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download .deb
                                </a>

                                @if($tweak->homepage)
                                    <a href="{{ $tweak->homepage }}" target="_blank"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150 ease-in-out">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                        </svg>
                                        Visit Homepage
                                    </a>
                                @endif

                                <form action="{{ route('dashboard.tweaks.destroy', $tweak) }}" method="POST"
                                    class="inline-block"
                                    onsubmit="return confirm('Are you sure you want to delete this tweak? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition duration-150 ease-in-out">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete Tweak
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Package Information -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                            </svg>
                            Package Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-start">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Package ID:</span>
                            <span
                                class="text-sm text-gray-900 dark:text-white font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $tweak->package }}</span>
                        </div>

                        @if($tweak->author)
                            <div class="flex justify-between items-start">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Author:</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $tweak->author }}</span>
                            </div>
                        @endif

                        @if($tweak->maintainer)
                            <div class="flex justify-between items-start">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Maintainer:</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $tweak->maintainer }}</span>
                            </div>
                        @endif

                        @if($tweak->architecture)
                            <div class="flex justify-between items-start">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Architecture:</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $tweak->architecture }}</span>
                            </div>
                        @endif

                        @if($tweak->installed_size)
                            <div class="flex justify-between items-start">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Installed Size:</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $tweak->installed_size }} KB</span>
                            </div>
                        @endif

                        @if($tweak->depends)
                            <div class="flex justify-between items-start">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Dependencies:</span>
                                <span class="text-sm text-gray-900 dark:text-white text-right">{{ $tweak->depends }}</span>
                            </div>
                        @endif

                        <div
                            class="flex justify-between items-start pt-4 border-t border-gray-200 dark:border-gray-700">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Uploaded:</span>
                            <span
                                class="text-sm text-gray-900 dark:text-white">{{ $tweak->created_at->format('F d, Y \a\t h:i A') }}</span>
                        </div>

                        <div class="flex justify-between items-start">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated:</span>
                            <span
                                class="text-sm text-gray-900 dark:text-white">{{ $tweak->updated_at->format('F d, Y \a\t h:i A') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                            </svg>
                            Statistics & Files
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                <div
                                    class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">
                                    File Size</div>
                                <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                                    {{ $tweak->formatted_size }}
                                </div>
                            </div>

                            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                                <div
                                    class="text-xs font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wide mb-1">
                                    Changelogs</div>
                                <div class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                                    {{ $tweak->changeLogs->count() }}
                                </div>
                            </div>
                        </div>

                        @php
                            $dataFiles = is_array($tweak->data_files) ? $tweak->data_files : json_decode($tweak->data_files, true);
                        @endphp
                        @if($dataFiles && is_array($dataFiles) && count($dataFiles) > 0)
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Data Files:</div>
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 max-h-48 overflow-y-auto">
                                    <ul class="space-y-1 text-xs font-mono text-gray-700 dark:text-gray-300">
                                        @foreach(array_slice($dataFiles, 0, 10) as $file)
                                            <li class="flex items-center">
                                                <svg class="w-3 h-3 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $file }}
                                            </li>
                                        @endforeach
                                        @if(count($dataFiles) > 10)
                                            <li class="text-gray-500 italic">... and {{ count($dataFiles) - 10 }} more
                                                files</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Changelogs Section -->
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z"
                                clip-rule="evenodd" />
                        </svg>
                        Version History & Changelogs
                        <span class="ml-auto text-sm font-normal text-gray-500 dark:text-gray-400">
                            {{ $tweak->changeLogs->count() }} {{ Str::plural('entry', $tweak->changeLogs->count()) }}
                        </span>
                    </h3>
                </div>

                <div class="p-6">
                    @forelse($tweak->changeLogs as $index => $changelog)
                        <div
                            class="relative pb-8 {{ !$loop->last ? 'border-l-2 border-gray-200 dark:border-gray-700 ml-4' : 'ml-4' }}">
                            <!-- Timeline dot -->
                            <div class="absolute left-0 -ml-[1.1rem] mt-1.5 h-8 w-8 rounded-full border-4 border-white dark:border-gray-800
                                                        {{ $index === 0 ? 'bg-green-500' : 'bg-blue-500' }}
                                                        flex items-center justify-center shadow-lg">
                                @if($index === 0)
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>

                            <!-- Changelog card -->
                            <div
                                class="ml-6 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 rounded-lg p-5 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-3">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                                                     {{ $index === 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                            Version {{ $changelog->version }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $changelog->created_at->format('F d, Y \a\t h:i A') }}
                                        </span>
                                    </div>
                                </div>
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
                        </div>
                    @empty
                        <p class="text-gray-600 dark:text-gray-400">No changelog entries available for this tweak.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>