<?php $__env->startSection('title', 'Terminal'); ?>

<?php $__env->startSection('content'); ?>
    <section class="page-content-wrapper">
        <div class="container-fluid">
            <h1>Networks list</h1>
            <?php if($model->networks !== false): ?>
                <div class="list-group">
                    <?php $__currentLoopData = $model->networks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $network): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="/admin/network/<?php echo e($network->mac); ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><?php echo e($network->ipv4); ?> - <?php echo e($network->ipv6); ?></span>
                            <span class="badge badge-primary badge-pill"><?php echo e($network->mac); ?></span>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin/layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>