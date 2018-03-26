<?php $__env->startSection('title', 'Signup'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <?php if(!empty($_SESSION["errors"])): ?>
        <div class="row col-12 alert alert-danger" role="alert">
            <?php $__currentLoopData = $_SESSION["errors"]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo e($error); ?>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
    <?php if(!empty($_SESSION["success"])): ?>
        <div class="row col-12 alert alert-success" role="alert">
            <?php $__currentLoopData = $_SESSION["success"]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $success): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo e($success); ?>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
    <div class="row">
        <form class="col-md-6 m-auto" method="POST">
            <?php echo csrf_token(); ?>

            <div class="form-group">
                <label for="password">Username</label>
                    <input type="text" class="form-control" id="uname" name="username" aria-describedby="usernameHelp" placeholder="Enter username" value="<?php echo e(ifsetor($_SESSION["data"]["username"], "")); ?>">
            </div>
            <div class="form-group">
                <label for="password">Email address</label>
                <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email" value="<?php echo e(ifsetor($_SESSION["data"]["email"], "")); ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password">
            </div>
            <div class="text-muted">By clicking on Sign up, you agree to <a href="/about/tos" target="_blank">SMN's terms & conditions</a></div>

            <br>
            <button type="submit" class="btn btn-primary col-12">Sign Up</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('../layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>