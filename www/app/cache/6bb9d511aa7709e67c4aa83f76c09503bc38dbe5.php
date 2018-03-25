<?php $__env->startSection('title', 'Terminal'); ?>

<?php $__env->startSection('styles'); ?>
<link rel="stylesheet" href="/assets/css/terminal.css">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <section class="page-content-wrapper container-fluid d-flex flex-column h-100">
        <section>
            <h1>Terminal <?php echo e($model->terminals[0]->mac); ?></h1>
            <h5><?php echo e($model->users[0]->username); ?> - <?php echo e($model->users[0]->email); ?></h5>
            <br>
        </section>
        <div class="card h-100">
            <div class="terminal" id="terminal-container">
                <div class="terminal-content" id="terminal-content-user">
                    </div>
                    <div id="terminal-content-response">
                    <div id="terminal-user">user@user:~ $
                        <span class="terminal-input" id="terminal-input" contenteditable="true" spellcheck="false"></span>
                        <span class="terminal-caret">█</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script src="/assets/js/terminal.js"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin/layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>