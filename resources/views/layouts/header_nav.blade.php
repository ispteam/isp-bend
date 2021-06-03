<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
<!-- ======= Header & Navigation Bar ======= -->
    <header class="header_section">
        <div class="container-fluid">
            <nav>
                <input id="nav-toggle" type="checkbox">
                <div class="logo"><a href="#home"><strong><h1>ISP</h1></strong></a></div>
                <ul class="links">
                    <li><a href="#home">Home</a></li>
		            <li><a href="#about">About</a></li>
		            <li><a href="#requests">Requests</a></li>
		            <li><a href="#suppliers">For Suppliers</a></li>
		            <li><a href="#contact">Contact</a></li>
                    <li class="sign"><li><a href="#login">Login</a></li> <li><a href="#sign_in">Sign In</a></li></li>
                </ul>
                <label for="nav-toggle" class="icon-burger">
		            <div class="line"></div>
	            	<div class="line"></div>
	            	<div class="line"></div>
                </label>
            </nav>
        </div>
    </header>
<!-- End Header & Navbar -->
</body>
</html>
