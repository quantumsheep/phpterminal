<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>alPH - @yield('title')</title>

    <link rel="stylesheet" href="https://unpkg.com/bootstrap-material-design@4.1.1/dist/css/bootstrap-material-design.min.css"
        integrity="sha384-wXznGJNEXNG1NFsbm0ugrLFMQPWswR3lds2VeinahP8N0zJw9VWSopbjv2x7WCvX" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" integrity="sha384-3AB7yXWz4OeoZcPbieVW64vVXEwADiYyAEhwilzWsLw+9FgqpyjjStpPnpBO8o8S"
        crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/style.css?{{ uniqid(rand()) }}">
    @yield('styles')

    <script>console.log("Welcome to alPH Terminal.");</script>
</head>

<body class="d-flex flex-column">
    <header>
        <nav class="container">
            <a href="/">
                <img src="/assets/img/alph-logo-xl.png" alt="Logo" class="h-100 p-1">
            </a>
            <ul class="nav nav-tabs bg-dark d-inline-flex float-right">
                <li class="nav-item">
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
        </nav>
    </header>
    @yield('content')
    <footer>
        <div>Copyright alPH 2018</div>
    </footer>

    @yield('script')
</body>

</html>