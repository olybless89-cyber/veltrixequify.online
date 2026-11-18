@php
    // Social share (WhatsApp / Facebook / Telegram / X) image.
    // Crawlers are strict: they need an absolute https URL, a valid MIME type,
    // and ideally explicit width/height. getFile() appends a "?v=" cache-buster,
    // which previously leaked into og:image:type ("image/png?v=123") and broke
    // previews, so the values are built cleanly here.
    $ogFile    = config('location.logoIcon.path') . config('seo.meta_image');
    $ogAbs     = base_path($ogFile);
    $ogExists  = config('seo.meta_image') && is_file($ogAbs);
    $ogImage   = $ogExists ? asset($ogFile) . '?v=' . @filemtime($ogAbs) : asset(config('location.logoIcon.path') . 'logo.png');
    $ogExt     = strtolower(pathinfo($ogExists ? $ogAbs : 'logo.png', PATHINFO_EXTENSION));
    $ogMime    = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'][$ogExt] ?? 'image/png';
    $ogSize    = $ogExists ? @getimagesize($ogAbs) : false;
    $ogTitle   = config('seo.social_title') ?: $basic->site_title;
    $ogDesc    = config('seo.social_description') ?: config('seo.meta_description');
@endphp
<meta name="description" content="{{ config('seo.meta_description')}}">
<meta name="keywords" content="{{ config('seo.meta_keywords') }}">
<link rel="shortcut icon" href="{{getFile(config('location.logoIcon.path').'favicon.png') }}" type="image/x-icon">
{{--<!-- Apple Stuff -->--}}
<link rel="apple-touch-icon" href="{{getFile(config('location.logoIcon.path').'logo.png') }}">
<title>@lang($basic->site_title) | @yield('title')</title>
<link rel="icon" type="image/png" sizes="16x16" href="{{ getFile(config('location.logoIcon.path').'favicon.png')}}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="@lang($basic->site_title) | @yield('title')">
{{--<!-- Google / Search Engine Tags -->--}}
<meta itemprop="name" content="{{ $ogTitle }}">
<meta itemprop="description" content="{{ $ogDesc }}">
<meta itemprop="image" content="{{ $ogImage }}">
{{--<!-- Open Graph (WhatsApp, Facebook, Telegram, LinkedIn) -->--}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $basic->site_title }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDesc }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:secure_url" content="{{ $ogImage }}">
<meta property="og:image:type" content="{{ $ogMime }}">
@if($ogSize)
<meta property="og:image:width" content="{{ $ogSize[0] }}">
<meta property="og:image:height" content="{{ $ogSize[1] }}">
@endif
<meta property="og:image:alt" content="{{ $ogTitle }}">
<meta property="og:locale" content="en_US">
{{--<!-- Twitter / X -->--}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDesc }}">
<meta name="twitter:image" content="{{ $ogImage }}">
<link rel="image_src" href="{{ $ogImage }}">
