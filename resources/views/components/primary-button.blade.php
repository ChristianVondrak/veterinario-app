<button {{ $attributes->merge(['type' => 'submit', 'class' => 'w-full px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg shadow-lg shadow-teal-500/30 hover:shadow-teal-500/40 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition transform hover:-translate-y-0.5 active:translate-y-0']) }}>
    {{ $slot }}
</button>
