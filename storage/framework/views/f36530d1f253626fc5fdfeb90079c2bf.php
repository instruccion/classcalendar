<?php $__env->startComponent('mail::message'); ?>
# Invitación a Curso

Hola <?php echo e($programacion->instructor->nombre ?? 'Instructor'); ?>,

Se te ha asignado el siguiente curso:

- **Curso:** <?php echo e($programacion->curso->nombre ?? 'Sin nombre'); ?>

- **Grupo:** <?php echo e($programacion->grupo->nombre ?? 'Sin grupo'); ?>

- **Fecha Inicio:** <?php echo e($programacion->fecha_inicio?->format('d/m/Y') ?? 'N/A'); ?>

- **Fecha Fin:** <?php echo e($programacion->fecha_fin?->format('d/m/Y') ?? 'N/A'); ?>

- **Horario:**
  <?php if($programacion->hora_inicio && $programacion->hora_fin): ?>
    <?php echo e($programacion->hora_inicio instanceof \Carbon\Carbon ? $programacion->hora_inicio->format('H:i') : $programacion->hora_inicio); ?> -
    <?php echo e($programacion->hora_fin instanceof \Carbon\Carbon ? $programacion->hora_fin->format('H:i') : $programacion->hora_fin); ?>

  <?php else: ?>
    N/A
  <?php endif; ?>
- **Lugar:** <?php echo e($programacion->aula->ubicacion ?? 'No asignado'); ?>


<?php $__env->startComponent('mail::button', ['url' => route('mi-agenda.confirmar', ['programacion' => $programacion->id])]); ?>
Confirmar Asistencia
<?php echo $__env->renderComponent(); ?>

Gracias por tu compromiso.

<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/emails/instructores/invitacion.blade.php ENDPATH**/ ?>