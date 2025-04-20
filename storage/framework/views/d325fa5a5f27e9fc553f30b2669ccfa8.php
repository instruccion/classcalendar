<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['type' => 'success', 'message']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['type' => 'success', 'message']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $typeClasses = match($type) {
        'success' => 'bg-green-500',
        'error' => 'bg-red-500',
        'warning' => 'bg-yellow-500',
        'info' => 'bg-blue-500',
        default => 'bg-gray-700',
    };
?>

<div x-data="{ show: true }" x-show="show"
     x-init="setTimeout(() => show = false, 5000)"
     class="fixed top-5 right-5 z-50 w-auto max-w-xs shadow-lg rounded-md p-4 text-white <?php echo e($typeClasses); ?>"
     x-transition:enter="transition ease-out duration-300"
     x-transition:leave="transition ease-in duration-300"
>
    <div class="flex justify-between items-start">
        <div class="text-sm font-medium">
            <?php echo e($message); ?>

        </div>
        <button @click="show = false" class="ml-4 text-white text-xl leading-none focus:outline-none">&times;</button>
    </div>
</div>
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/components/toast.blade.php ENDPATH**/ ?>