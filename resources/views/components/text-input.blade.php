@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full bg-white/50 dark:bg-slate-900/50 border border-gray-300 dark:border-slate-600 text-gray-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-xl shadow-sm backdrop-blur-sm transition-all']) }}>
