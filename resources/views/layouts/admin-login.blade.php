<!doctype html>
<html lang="id"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $title ?? 'Masuk Admin · Disporabudpar Jember' }}</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
<script>try{var t=localStorage.getItem('admin-tema');if(t)document.documentElement.setAttribute('data-theme',t)}catch(e){}</script>
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">@livewireStyles
</head><body>
<main class="login-wrap">{{ $slot }}</main>
@livewireScripts
</body></html>
