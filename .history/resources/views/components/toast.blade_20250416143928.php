@props(['type' => 'success', 'message'])

@php
    $typeClasses = match($type) {
        'success' => 'bg-green-500',
        'error' => 'bg-red-500',
        'warning' => 'bg-yellow-500',
        'info' => 'bg-blue-500',
        default => 'bg-gray-700',
    };
@endphp

<div x-data="{ show: true }" x-show="show"
     x-init="setTimeout(() => show = false, 5000)"
     class="fixed top-5 right-5 z-50 w-auto max-w-xs shadow-lg rounded-md p-4 text-white {{ $typeClasses }}"
     x-transition:enter="transition ease-out duration-300"
     x-transition:leave="transition ease-in duration-300"
>
    <div class="flex justify-between items-start">
        <div class="text-sm font-medium">
            {{ $message }}
        </div>
        <button @click="show = false" class="ml-4 text-white text-xl leading-none focus:outline-none">&times;</button>
    </div>
</div>
