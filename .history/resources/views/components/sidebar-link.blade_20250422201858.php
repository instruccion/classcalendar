@props(['active' => false, 'href' => '#', 'icon' => 'mdi-help-circle-outline'])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-3 py-2.5 text-sm font-medium rounded-md bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 group' // Clases activas
            : 'flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white group'; // Clases inactivas
@endphp

<a {{ $attributes->merge(['href' => $href, 'class' => $classes]) }}>
    <i class="mdi {{ $icon }} text-xl mr-3 flex-shrink-0 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-300 {{ $active ? 'text-indigo-500 dark:text-indigo-300' : '' }}"></i>
    <span class="sidebar-link-text">{{ $slot }}</span>
</a>
