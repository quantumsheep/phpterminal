<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>alPH - @yield('title')</title>
    
    <link rel="icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" integrity="sha384-3AB7yXWz4OeoZcPbieVW64vVXEwADiYyAEhwilzWsLw+9FgqpyjjStpPnpBO8o8S" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/style.css?{{ uniqid(rand()) }}">
    @yield('styles')

    <script>console.log("Welcome to alPH Terminal.");</script>
</head>

<body>
    <header class="container">
        <nav class="navbar navbar-expand navbar-dark">
            <div class="navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="navbar-brand" href="/">
                            <img src="/assets/img/alph-logo-xl.png" alt="Logo" class="logo">
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item d-none d-md-block d-lg-block">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    @if(empty($_SESSION["account"]))
                    <li class="nav-item">
                        <a class="nav-link" href="/signin">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/signup">Signup</a>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="nav-link" href="/terminal">Terminal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/account">Account</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout">Logout</a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" href="/about/tos">About</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    @yield('content')
    <footer>
        <div>Copyright alPH 2018</div>
    </footer>

    @yield('script')
</body>

</html>