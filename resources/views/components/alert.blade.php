@props(['type' => 'info', 'message' => null])

@if ($message)
    @php
        $baseClasses = 'px-4 py-3 rounded text-sm mb-4';
        $typeClasses = match($type) {
            'success' => 'bg-green-100 text-green-800 border border-green-300',
            'error' => 'bg-red-100 text-red-800 border border-red-300',
            'warning' => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
            'info' => 'bg-blue-100 text-blue-800 border border-blue-300',
            default => 'bg-gray-100 text-gray-800 border border-gray-300',
        };
    @endphp

    <div {{ $attributes->merge(['class' => "$baseClasses $typeClasses"]) }}>
        {{ $message }}
    </div>
@endif
