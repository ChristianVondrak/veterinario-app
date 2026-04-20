@props([
    'id',           {{-- Unique identifier for this dialog --}}
    'title',        {{-- Dialog title text --}}
    'body',         {{-- Dialog body text (HTML allowed) --}}
    'confirmLabel'  => 'Confirmar',
    'confirmClass'  => 'bg-red-600 hover:bg-red-700 text-white',
    'cancelLabel'   => 'Cancelar',
    'action',       {{-- Form action URL --}}
    'method'        => 'DELETE',
])

{{--
    x-confirm-dialog
    ─────────────────────────────────────────────────────────────────
    Reusable confirmation dialog component.

    Usage (trigger button):
        <button x-data x-on:click.prevent.stop="$dispatch('open-confirm', '{{ $id }}')">
            Delete
        </button>

    Usage (component):
        <x-confirm-dialog
            id="delete-patient-5"
            title="Eliminar Paciente"
            :body="'¿Eliminar a <strong>'. $patient->name .'</strong>?'"
            :action="route('patients.destroy', $patient)"
        />
    ─────────────────────────────────────────────────────────────────
--}}

<div
    x-data="{ open: false }"
    x-on:open-confirm.window="if ($event.detail === '{{ $id }}') open = true"
    x-on:keydown.escape.window="open = false"
    x-on:click.stop
>
    {{-- Teleport renders the dialog at the end of <body> so it's
         completely isolated from any parent's CSS (cursor, whitespace, etc.) --}}
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            x-on:open-confirm.window="if ($event.detail === '{{ $id }}') open = true"
            x-on:keydown.escape.window="open = false"
            class="relative z-50"
            style="cursor: default;"
            aria-modal="true"
            role="dialog"
            aria-labelledby="dialog-title-{{ $id }}"
        >
            {{-- Backdrop --}}
            <div
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                x-on:click="open = false"
                aria-hidden="true"
            ></div>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 sm:p-6 text-center">
                    {{-- Panel --}}
                    <div
                        x-show="open"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl border border-slate-200 transition-all w-full max-w-sm"
                        x-on:click.stop
                    >
                        <form
                            method="POST"
                            action="{{ $action }}"
                            class="p-6 sm:p-8"
                        >
                            @csrf
                            @method($method)

                            {{-- Icon + Text --}}
                            <div class="sm:flex sm:items-start gap-4">
                                <div class="mx-auto flex h-14 w-14 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100">
                                    <svg class="h-7 w-7 sm:h-6 sm:w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-4 text-center sm:ml-2 sm:mt-0 sm:text-left flex-1 min-w-0">
                                    <h3 class="text-lg font-bold leading-6 text-slate-900" id="dialog-title-{{ $id }}">
                                        {{ $title }}
                                    </h3>
                                    <div class="mt-3">
                                        <p class="text-sm text-slate-600 leading-relaxed break-words whitespace-normal">
                                            {!! $body !!}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                                <button
                                    type="button"
                                    x-on:click="open = false"
                                    class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-3 sm:px-4 sm:py-2 rounded-lg border border-slate-300 bg-white text-base sm:text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 transition"
                                >
                                    {{ $cancelLabel }}
                                </button>
                                <button
                                    type="submit"
                                    class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-3 sm:px-4 sm:py-2 rounded-lg border border-transparent text-base sm:text-sm font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition {{ $confirmClass }}"
                                >
                                    {{ $confirmLabel }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
