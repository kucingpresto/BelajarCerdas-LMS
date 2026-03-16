<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title id="title-icon-web" data-school-name="{{ Auth::check() ? (Auth::user()->StudentProfile?->SchoolPartner?->nama_sekolah ?? Auth::user()->SchoolStaffProfile?->SchoolPartner?->nama_sekolah) 
        : null }}"></title>
    <link rel="shortcut icon" type="image/svg" href="{{ asset('assets/images/favicon/favicon.svg') }}">

    <!-- Your compiled app.css (includes Tailwind, DaisyUI if configured) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Your custom BelajarCerdas.css (consider merging into app.css if possible) -->
    <link rel="stylesheet" href="{{ asset('assets/css/BelajarCerdas.css') }}">

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Asynchronously load Font Awesome (or self-host) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    </noscript>
</head>
<body>
    
</body>
</html>

<script>
    const titleIconWeb = document.getElementById('title-icon-web');
    const schoolPartnerName = titleIconWeb.getAttribute('data-school-name');

    if (schoolPartnerName) {
        document.title = `LMS - ${schoolPartnerName}`
    } else {
        document.title = 'LMS - BelajarCerdas'
    }
</script>