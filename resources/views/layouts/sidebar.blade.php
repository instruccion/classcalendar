<div
    x-data="{ expanded: localStorage.getItem('sidebarExpanded') === 'true' }"
    x-init="$watch('expanded', val => localStorage.setItem('sidebarExpanded', val))"
    class="flex flex-col bg-white border-r transition-all duration-300"
    :class="expanded ? 'w-64' : 'w-20'"
>
    <button
        class="p-2 hover:bg-gray-100 transition"
        @click="expanded = !expanded"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor">
            <use x-bind:href="expanded ? '#icon-collapse' : '#icon-expand'" />
        </svg>
    </button>

    <nav class="flex-1 space-y-1 mt-4">
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor"><use href="#icon-dashboard" /></svg>
            <span x-show="expanded" class="ml-3">Dashboard</span>
        </a>
        <a href="{{ route('admin.programaciones.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor"><use href="#icon-calendar" /></svg>
            <span x-show="expanded" class="ml-3">Programaciones</span>
        </a>
        <!-- Repite con otros ítems -->
    </nav>
</div>

<!-- SVGs ocultos que puedes definir en tu layout -->
<svg style="display: none;">
    <symbol id="icon-dashboard" viewBox="0 0 24 24">
        <path d="M3 3h18v18H3z" />
    </symbol>
    <symbol id="icon-calendar" viewBox="0 0 24 24">
        <path d="M8 7V3m8 4V3M3 11h18M5 5h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
    </symbol>
    <symbol id="icon-collapse" viewBox="0 0 24 24">
        <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>
    <symbol id="icon-expand" viewBox="0 0 24 24">
        <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>
</svg>
