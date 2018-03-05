<?php $__env->startSection('title', 'Terminal'); ?>

<?php $__env->startSection('content'); ?>
    <div class="terminal container">
        <div class="terminal-content">
            <div>user@user:~ $<input type="text" id="terminal-input"></div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script src="/assets/js/terminal.js"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>