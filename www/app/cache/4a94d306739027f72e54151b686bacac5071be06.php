<?php $__env->startSection('title', 'Terminal'); ?>

<?php $__env->startSection('content'); ?>
    <div class="terminal container" id="terminal-container">
        <div class="terminal-content" id="terminal-content-user">
            </div>
            <div id="terminal-content-response">
            <div id="terminal-user">user@user:~ $
            <input type="text" class="terminal-input" id="terminal-input">
        </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script src="/assets/js/terminal.js"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>