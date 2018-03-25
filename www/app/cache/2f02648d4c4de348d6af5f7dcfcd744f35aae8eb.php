<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>alPH - <?php echo $__env->yieldContent('title'); ?></title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <?php echo $__env->yieldContent('styles'); ?>
</head>

<body class="wrapper toggled">
    <header>
        <nav class="sidebar-wrapper">
            <ul class="sidebar-nav">
                <li class="sidebar-brand">
                    <a href="/" class="logo">
                        <img src="/assets/img/alph-logo-xl.png" class="logo">
                    </a>
                </li>
                <li>
                    <a href="/admin">Dashboard</a>
                </li>
                <li>
                    <a href="/admin/terminal">Terminals</a>
                </li>
                <li>
                    <a href="/admin/networks">Networks</a>
                </li>
                <li>
                    <a href="/admin/users">Users</a>
                </li>
            </ul>
        </nav>
    </header>

    <?php echo $__env->yieldContent('content'); ?>

    <?php echo $__env->yieldContent('script'); ?>
</body>

</html>
