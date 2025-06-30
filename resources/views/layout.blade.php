<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Range Reports' }}</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" rel="stylesheet">
    <style>.prose img{max-width:100%;}</style>
</head>
<body class="container">
<nav><strong><a href="{{ url('/') }}">Range Reports</a></strong></nav>
<main>@yield('content')</main>
<footer class="text-small">© {{ date('Y') }} Wither Rebirth</footer>
</body>
</html>

