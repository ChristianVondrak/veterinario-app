@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500 placeholder:text-slate-400 transition']) }}>
