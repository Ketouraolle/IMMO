<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EstateHub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @include('partials.ui-styles')
    @include('partials.public-styles')
</head>
<body>
<div class="auth-shell">
    <div class="container">
        <div class="card auth-card">
            <div class="card-body p-4">
                <div class="auth-brand mb-4"><span class="site-brand__mark"><i class="bi bi-house-door-fill"></i></span> EstateHub</div>
                @yield('content')
                <div class="text-center mt-3">
                    <a href="{{ route('public.properties.index') }}" class="back-link mb-0"><i class="bi bi-arrow-left"></i> Back to listings</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
