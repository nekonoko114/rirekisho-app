<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', '履歴書アプリ') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-50 dark:bg-slate-900 text-gray-800 dark:text-gray-100 selection:bg-indigo-500 selection:text-white">
        <div class="min-h-screen flex flex-col relative overflow-hidden">
            <!-- Geometric Grid Background -->
            <div class="absolute inset-0 -z-20 h-full w-full bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px] [mask-image:radial-gradient(ellipse_80%_80%_at_50%_50%,#000_70%,transparent_100%)]"></div>
            
            <!-- Background Decoration (Glassmorphism blobs) -->
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob dark:mix-blend-screen -z-10"></div>
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-emerald-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000 dark:mix-blend-screen -z-10"></div>
            <div class="absolute -bottom-32 left-1/2 w-96 h-96 bg-purple-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-4000 dark:mix-blend-screen -z-10"></div>

            <nav class="container mx-auto px-4 sm:px-6 py-4 sm:py-6 flex items-center justify-between z-10 relative">
                <a href="/" class="flex items-center gap-2 sm:gap-3 group">
                    <div class="flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-8 h-8 sm:w-10 sm:h-10" viewBox="0 0 651 610" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M495.9 220.323L336.19 128.08V-0.000488281L651.109 181.815V433.916L495.9 523.504V220.323Z" fill="#FF2D20"/>
                            <path d="M495.9 523.497L336.19 615.747V487.653L495.9 395.403V523.497Z" fill="#FF2D20"/>
                            <path d="M336.19 128.081L176.48 220.324V523.504L336.19 615.748V487.668L193.303 405.163V230.04L336.19 147.534V128.081Z" fill="#CB3837"/>
                            <path d="M154.91 382.973L0 472.434V220.324L154.91 130.863V382.973Z" fill="#FF2D20"/>
                        </svg>
                    </div>
                    <span class="font-bold text-lg sm:text-xl tracking-tight hidden sm:block">{{ config('app.name', '履歴書アプリ') }}</span>
                </a>
                <div class="flex items-center gap-3 sm:gap-4">
                    @if(Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors whitespace-nowrap">{{ __('Dashboard') }}</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors whitespace-nowrap">ログイン</a>
                            @if(Route::has('register'))
                                <a href="{{ route('register') }}" class="text-xs sm:text-sm font-medium px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-white/50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all backdrop-blur-sm whitespace-nowrap">新規登録</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </nav>

            <header class="container mx-auto px-4 sm:px-6 py-12 md:py-24 z-10 relative flex-grow flex items-center">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center w-full">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-sm font-medium mb-6 border border-indigo-200 dark:border-indigo-800/50">
                            <span class="flex h-2 w-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                            簡単・スピーディに作成
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-6 leading-tight">
                            あなたの次の一歩を、<br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-emerald-500 dark:from-indigo-400 dark:to-emerald-400">よりシンプルに</span>
                        </h1>
                        <p class="text-base sm:text-lg text-gray-600 dark:text-gray-300 mb-8 leading-relaxed">
                            履歴書をオンラインで作成・管理し、ワンクリックで美しいPDFとして出力できます。<br class="hidden sm:block">煩わしいフォーマット調整はもう不要です。
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 text-base font-medium text-white bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all duration-300">
                                無料で始める
                            </a>
                            <a href="{{ route('resumes.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 text-base font-medium text-white bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:-translate-y-0.5 transition-all duration-300">
                                履歴書を作成
                            </a>
                            <a href="{{ route('cvs.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 text-base font-medium text-white bg-gradient-to-r from-teal-600 to-teal-500 hover:from-teal-500 hover:to-teal-400 rounded-xl shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 hover:-translate-y-0.5 transition-all duration-300">
                                職務経歴書を作成
                            </a>
                            <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 text-base font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 rounded-xl shadow-sm hover:-translate-y-0.5 transition-all duration-300">
                                ログイン
                            </a>
                        </div>
                    </div>

                    <div class="flex justify-center lg:justify-end relative perspective-1000">
                        <!-- Resume Mockup Card -->
                        <div class="w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 p-6 transform rotate-y-[-5deg] rotate-x-[2deg] hover:rotate-0 transition-transform duration-700 ease-out z-10 backdrop-blur-xl bg-white/90 dark:bg-slate-800/90">
                            <div class="flex justify-between items-start mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
                                <div>
                                    <div class="h-6 w-32 bg-gray-200 dark:bg-slate-700 rounded-md mb-2"></div>
                                    <div class="h-3 w-24 bg-gray-100 dark:bg-slate-600 rounded-md"></div>
                                </div>
                                <div class="h-16 w-12 bg-indigo-100 dark:bg-indigo-900/50 rounded flex items-center justify-center">
                                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <div class="h-3 w-16 bg-emerald-100 dark:bg-emerald-900/50 rounded mb-2"></div>
                                    <div class="h-2 w-full bg-gray-100 dark:bg-slate-700 rounded mb-1"></div>
                                    <div class="h-2 w-5/6 bg-gray-100 dark:bg-slate-700 rounded"></div>
                                </div>
                                <div>
                                    <div class="h-3 w-16 bg-indigo-100 dark:bg-indigo-900/50 rounded mb-2"></div>
                                    <div class="h-2 w-full bg-gray-100 dark:bg-slate-700 rounded mb-1"></div>
                                    <div class="h-2 w-4/6 bg-gray-100 dark:bg-slate-700 rounded mb-1"></div>
                                    <div class="h-2 w-5/6 bg-gray-100 dark:bg-slate-700 rounded"></div>
                                </div>
                                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                                    <div class="h-8 w-24 bg-indigo-500 rounded-lg shadow-sm"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Decorative floating elements -->
                        <div class="absolute -top-6 -right-6 w-24 h-24 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl shadow-xl transform rotate-12 opacity-60 blur-[2px] -z-10"></div>
                        <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-gradient-to-br from-indigo-400 to-purple-600 rounded-full shadow-xl opacity-50 blur-[2px] -z-10"></div>
                    </div>
                </div>
            </header>

            <section class="relative z-10 py-20 bg-white/50 dark:bg-slate-900/50 backdrop-blur-md border-t border-gray-200/50 dark:border-slate-800/50">
                <div class="container mx-auto px-6">
                    <div class="text-center max-w-2xl mx-auto mb-16">
                        <h2 class="text-3xl font-bold mb-4 text-gray-900 dark:text-white">主な特徴</h2>
                        <p class="text-gray-600 dark:text-gray-400">直感的でシンプルな操作性。あなたの魅力を最大限に引き出す機能が揃っています。</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Feature 1 -->
                        <div class="group p-8 bg-white/40 dark:bg-slate-800/40 backdrop-blur-xl rounded-2xl shadow-lg border border-white/50 dark:border-slate-700/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:bg-white/60 dark:hover:bg-slate-800/60">
                            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold mb-3 text-gray-900 dark:text-white">美しいテンプレート</h3>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed font-medium">印刷に最適化された美しくプロフェッショナルなテンプレートをご用意。すぐに見栄えの良い履歴書が完成します。</p>
                        </div>
                        
                        <!-- Feature 2 -->
                        <div class="group p-8 bg-white/40 dark:bg-slate-800/40 backdrop-blur-xl rounded-2xl shadow-lg border border-white/50 dark:border-slate-700/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:bg-white/60 dark:hover:bg-slate-800/60">
                            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold mb-3 text-gray-900 dark:text-white">ワンクリックPDF出力</h3>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed font-medium">作成した履歴書は、レイアウト崩れなくワンクリックでPDFファイルとしてダウンロード可能です。</p>
                        </div>

                        <!-- Feature 3 -->
                        <div class="group p-8 bg-white/40 dark:bg-slate-800/40 backdrop-blur-xl rounded-2xl shadow-lg border border-white/50 dark:border-slate-700/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:bg-white/60 dark:hover:bg-slate-800/60">
                            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold mb-3 text-gray-900 dark:text-white">セキュアな共有リンク</h3>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed font-medium">面接官や採用担当者に共有するための、公開用トークン付きURLを安全に生成できます。</p>
                        </div>
                    </div>
                </div>
            </section>

            <footer class="relative z-10 py-8 border-t border-white/20 dark:border-slate-700/50 text-center text-sm text-gray-600 dark:text-gray-300 bg-white/30 dark:bg-slate-900/30 backdrop-blur-md">
                <div class="container mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="font-medium">
                        &copy; {{ date('Y') }} {{ config('app.name', '履歴書アプリ') }}. All rights reserved.
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
