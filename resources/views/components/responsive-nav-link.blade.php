@props(['active'])

@php
$classes = ($active ?? false)
    ? 'block w-full ps-4 pe-4 py-3 border-l-4 border-teal-500 text-base font-medium text-teal-700 bg-teal-50 focus:outline-none focus:bg-teal-50 focus:text-teal-800 transition duration-150 ease-in-out'
    : 'block w-full ps-4 pe-4 py-3 border-l-4 border-transparent text-base font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:bg-slate-50 focus:text-slate-900 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
