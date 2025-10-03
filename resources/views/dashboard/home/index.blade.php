<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Dashboard') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Welcome back! Here's what's happening with your repository.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    <span class="w-2 h-2 mr-2 bg-green-500 rounded-full animate-pulse"></span>
                    {{ __(' System Online') }}
                </span>
                <a href="{{ route('dashboard.tweaks.push') }}"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7v14" />
                    </svg>
                    Push Tweaks
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Quick Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Tweaks Card -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Total Tweaks
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                            {{ $stats['total_tweaks'] ?? 0 }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 px-6 py-3">
                        <a href="{{ route('dashboard.tweaks.index') }}"
                            class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                            View all →
                        </a>
                    </div>
                </div>

                <!-- Repository Size Card -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Repository Size
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                            {{ $stats['total_size'] ?? '0 B' }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 px-6 py-3">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Across all packages
                        </span>
                    </div>
                </div>

                <!-- Categories Card -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Categories
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                            {{ $stats['sections'] ?? 0 }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 px-6 py-3">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Unique sections
                        </span>
                    </div>
                </div>

                <!-- Recent Uploads Card -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Recent Uploads
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                            {{ $stats['recent_uploads'] ?? 0 }}
                                        </div>
                                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                                            (7 days)
                                        </span>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 px-6 py-3">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Last week activity
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Recent Activity Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Quick Actions Card -->
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Quick Actions
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Common tasks and shortcuts
                        </p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="{{ route('dashboard.tweaks.create') }}"
                                class="flex items-center p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-600 dark:hover:bg-gray-750 transition-colors duration-150 group">
                                <div
                                    class="flex-shrink-0 bg-blue-100 dark:bg-blue-900/30 rounded-lg p-3 group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition-colors duration-150">
                                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div class="ml-4 flex-1">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        Upload New Tweak
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Add a new package to your repository
                                    </p>
                                </div>
                                <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>

                            <a href="{{ route('dashboard.tweaks.index') }}"
                                class="flex items-center p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-600 dark:hover:bg-gray-750 transition-colors duration-150 group">
                                <div
                                    class="flex-shrink-0 bg-green-100 dark:bg-green-900/30 rounded-lg p-3 group-hover:bg-green-200 dark:group-hover:bg-green-900/50 transition-colors duration-150">
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <div class="ml-4 flex-1">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        Manage Tweaks
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        View and edit existing packages
                                    </p>
                                </div>
                                <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>

                            <a href="#"
                                class="flex items-center p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-600 dark:hover:bg-gray-750 transition-colors duration-150 group">
                                <div
                                    class="flex-shrink-0 bg-purple-100 dark:bg-purple-900/30 rounded-lg p-3 group-hover:bg-purple-200 dark:group-hover:bg-purple-900/50 transition-colors duration-150">
                                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4 flex-1">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        View Repository Logs
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Check system activity and changes
                                    </p>
                                </div>
                                <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Info Card -->
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Repository Information
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            System details and configuration
                        </p>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-4">
                            <div class="flex items-center justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Repository Type
                                </dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white">
                                    APT Repository
                                </dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Server Status
                                </dt>
                                <dd>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <span class="w-1.5 h-1.5 mr-1.5 bg-green-500 rounded-full"></span>
                                        Operational
                                    </span>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Last Updated
                                </dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ now()->format('M d, Y') }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Total Downloads
                                </dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Coming Soon
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Storage Used</span>
                                <span
                                    class="font-semibold text-gray-900 dark:text-white">{{ $stats['total_size'] ?? '0 B' }}</span>
                            </div>
                            <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-2 rounded-full"
                                    style="width: 15%"></div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                15% of available storage
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Welcome Message -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-8 sm:px-10 sm:py-10">
                    <div class="flex items-center justify-between flex-wrap">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-2xl font-bold text-white">
                                Ready to manage your packages?
                            </h3>
                            <p class="mt-2 text-blue-100 text-sm max-w-2xl">
                                Your repository is up and running. Upload new tweaks, manage existing packages, or
                                configure your repository settings.
                            </p>
                        </div>
                        <div class="mt-4 sm:mt-0 sm:ml-6 flex-shrink-0">
                            <a href="{{ route('dashboard.tweaks.create') }}"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-blue-600 bg-white hover:bg-blue-50 transition-colors duration-150">
                                Get Started
                                <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>