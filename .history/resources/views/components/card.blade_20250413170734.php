<div {{ $attributes->merge(['class' => 'bg-white p-6 rounded-xl shadow-md border border-gray-200']) }}>
    @isset($title)
        <h2 class="text-lg font-semibold text-gray-700 mb-4">{{ $title }}</h2>
    @endisset

    {{ $slot }}
</div>
