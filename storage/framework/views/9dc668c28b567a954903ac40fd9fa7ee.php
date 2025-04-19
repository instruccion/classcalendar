<div <?php echo e($attributes->merge(['class' => 'bg-white p-6 rounded-xl shadow-md border border-gray-200'])); ?>>
    <?php if(isset($title)): ?>
        <h2 class="text-lg font-semibold text-gray-700 mb-4"><?php echo e($title); ?></h2>
    <?php endif; ?>

    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/components/card.blade.php ENDPATH**/ ?>