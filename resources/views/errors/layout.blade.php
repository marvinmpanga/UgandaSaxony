<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>@yield('code') — @yield('title') | Uganda–Saxony Partnership</title>
        <link rel="icon" type="image/png" href="/images/legacy/logo.png" />
        <style>
            body { font-family: system-ui, -apple-system, sans-serif; margin: 0; color: #0f2624; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; text-align: center; }
            .wrap { max-width: 28rem; }
            img { width: 100%; max-width: 24rem; height: auto; }
            p { margin: 1.5rem 0; font-size: 1.125rem; color: #556b68; line-height: 1.6; }
            a { display: inline-block; padding: 0.75rem 1.75rem; border-radius: 9999px; background: #20534f; color: #fff; text-decoration: none; font-size: 0.875rem; font-weight: 600; }
        </style>
    </head>
    <body>
        <div class="wrap">
            <img src="/images/404.svg" alt="@yield('title')" />
            <p>@yield('message')</p>
            <a href="{{ url('/') }}">Back to homepage</a>
        </div>
    </body>
</html>
