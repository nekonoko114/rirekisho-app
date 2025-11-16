<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!DOCTYPE html>
        <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">

                <title>{{ config('app.name', 'Laravel') }}</title>

                @vite(['resources/css/app.css', 'resources/js/app.js'])
            </head>
            <body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                <div class="min-h-screen flex flex-col">
                    <nav class="container mx-auto px-6 py-6 flex items-center justify-between">
                        <a href="/" class="flex items-center gap-3">
                            <x-application-logo class="h-8 w-auto" />
                            <span class="font-semibold">{{ config('app.name') }}</span>
                        </a>
                        <div class="flex items-center gap-4">
                            @if(Route::has('login'))
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="text-sm">{{ __('Dashboard') }}</a>
                                @else
                                    <a href="{{ route('login') }}" class="text-sm">{{ __('Log in') }}</a>
                                    @if(Route::has('register'))
                                        <a href="{{ route('register') }}" class="text-sm">{{ __('Register Now') }}</a>
                                    @endif
                                @endauth
                            @endif
                        </div>
                    </nav>

                    <header class="container mx-auto px-6 py-12">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                            <div>
                                <h1 class="text-3xl lg:text-4xl font-bold mb-4">{{ __('Your next career move, simplified') }}</h1>
                                <p class="text-gray-600 dark:text-gray-300 mb-6">{{ __('Create and manage resumes online, then export PDF with one click.') }}</p>

                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('register') }}" class="inline-flex items-center px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md shadow">{{ __('Start for free') }}</a>
                                    <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-3 border border-gray-300 dark:border-gray-700 rounded-md text-gray-700 dark:text-gray-200">{{ __('Log in') }}</a>
                                     <a href="{{ route('resumes.create') }}" class="inline-flex items-center px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md shadow">{{ __('Create Resume') }}</a>
                                </div>
                            </div>

                            <div class="flex justify-center lg:justify-end">
                                <div class="w-full max-w-sm bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                                    {{-- サンプルプレビュー/ロゴを表示 --}}
                                    <div class="mb-4">
                                        <svg class="w-full text-indigo-600 dark:text-indigo-400" viewBox="0 0 438 104" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M17.2036 -3H0V102.197H49.5189V86.7187H17.2036V-3Z" fill="currentColor" />
                                        </svg>
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300">{{ __('Preview sample resume and PDF output in your browser.') }}</div>
                                </div>
                            </div>
                        </div>
                    </header>

                    <section class="bg-white dark:bg-gray-800 py-12">
                        <div class="container mx-auto px-6">
                            <h2 class="text-2xl font-semibold mb-6">{{ __('Features') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="p-6 bg-gray-50 dark:bg-gray-900 rounded-lg shadow-sm">
                                    <h3 class="font-semibold mb-2">{{ __('Resume Templates') }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Beautiful, print-ready templates to get started quickly.') }}</p>
                                </div>
                                <div class="p-6 bg-gray-50 dark:bg-gray-900 rounded-lg shadow-sm">
                                    <h3 class="font-semibold mb-2">{{ __('PDF Export') }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Export clean, professional PDFs with a single click.') }}</p>
                                </div>
                                <div class="p-6 bg-gray-50 dark:bg-gray-900 rounded-lg shadow-sm">
                                    <h3 class="font-semibold mb-2">{{ __('Shareable Public Link') }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Generate a public token link to share your resume securely.') }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <footer class="mt-auto py-8 text-center text-sm text-gray-500">
                        &copy; {{ date('Y') }} {{ config('app.name') }}
                    </footer>
                </div>
            </body>
        </html>
                            </span>
