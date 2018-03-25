<?php $__env->startSection('title', 'Signin'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <?php if(!empty($_SESSION["errors"])): ?>
        <div class="row col-12 alert alert-danger" role="alert">
            <?php $__currentLoopData = $_SESSION["errors"]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo e($error); ?>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
    <div class="row">
        <form class="col-md-6 m-auto" method="POST">
            <?php echo csrf_token(); ?>

            <div class="form-group">
                <label for="password">Email address</label>
                <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email" value="<?php echo e(ifsetor($_SESSION["data"]["email"], "")); ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password">
            </div>

            <br>
            <button type="submit" class="btn btn-primary col-12">Sign In</button>
            <a href="/signup">Or create a new account here.</a>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('../layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>