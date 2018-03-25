<?php $__env->startSection('title', 'Terminal'); ?>

<?php $__env->startSection('content'); ?>
    <section class="page-content-wrapper">
        <div class="container-fluid">
            <h1>Dashboard</h1>
            <p>Welcome to alph Terminal dashboard</p>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin/layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>