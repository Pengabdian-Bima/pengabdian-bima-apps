<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Primary Meta Tags -->
    <title>UD Flamboyan</title>
    <meta name="title" content="UD Flamboyan">
    <meta name="description" content="E-Commerce UMKM UD Flamboyan - Produsen dan Penjualan Biskuit Ikan Huluu Khas Danau Limboto Gorontalo. Nikmati Camilan Sehat, Lezat, dan Bergizi Olahan Lokal Berkualitas.">
    <meta name="keywords" content="UD Flamboyan, Biskuit Ikan Huluu, Danau Limboto, Oleh-Oleh Gorontalo, Biskuit Ikan, UMKM Gorontalo, flamboyansonya, Kuliner Gorontalo, Pre-Order Biskuit">
    <meta name="author" content="UD Flamboyan">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ env('APP_URL') }}">

    <!-- Favicon & Icons -->
    <link rel="icon" href="{{ asset('img/biskuit.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('img/biskuit.png') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('img/biskuit.png') }}">
    <meta name="theme-color" content="#ea580c">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title" content="UD Flamboyan">
    <meta property="og:description" content="E-Commerce UMKM UD Flamboyan - Produsen dan Penjualan Biskuit Ikan Huluu Khas Danau Limboto Gorontalo. Nikmati Camilan Sehat, Lezat, dan Bergizi Olahan Lokal Berkualitas.">
    <meta property="og:image" content="{{ asset('img/biskuit.png') }}">
    <meta property="og:site_name" content="UD Flamboyan Sonya">
    <meta property="og:locale" content="id_ID">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ env('APP_URL') }}">
    <meta name="twitter:title" content="UD Flamboyan">
    <meta name="twitter:description" content="E-Commerce UMKM UD Flamboyan - Produsen dan Penjualan Biskuit Ikan Huluu Khas Danau Limboto Gorontalo. Nikmati Camilan Sehat, Lezat, dan Bergizi Olahan Lokal Berkualitas.">
    <meta name="twitter:image" content="{{ asset('img/biskuit.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="antialiased">
    @inertia
</body>
</html>
