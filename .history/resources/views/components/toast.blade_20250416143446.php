@props(['type' => 'success', 'message'])

@php
    $colors = [
        'success' => 'bg-green-100 text-green-800 border border-green-300',
        'error' => 'bg-red-100 text-red-800 border border-red-300',
        'warning' => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
        'info' => 'bg-blue-100 text-blue-800 border border-blue-300',
    ];
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 4000)"
    class="fixed top-20 right-6 z-50 max-w-xs w-full p-4 rounded shadow-lg transition ease-out duration-500"
    :class="'{{ $colors[$type] ?? $colors['info'] }}'"
>
    <div class="flex justify-between items-center">
        <span class="text-sm font-medium">{{ $message }}</span>
        <button @click="show = false" class="ml-2 text-xl leading-none">&times;</button>
    </div>
</div>
