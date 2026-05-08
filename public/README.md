# Rankgim favicon set — v4 parchment

## What's in here

| File | Purpose |
|---|---|
| `favicon.ico` | Multi-size (16/32/48), classic browser tab fallback |
| `favicon.svg` | Vector, used by modern browsers — scales crisply |
| `favicon-16x16.png` | Small PNG fallback |
| `favicon-32x32.png` | Small PNG fallback |
| `favicon-48x48.png` | Windows / older browser fallback |
| `apple-touch-icon.png` | 180×180, iOS home screen |
| `android-chrome-192x192.png` | Android / PWA |
| `android-chrome-512x512.png` | Android / PWA / splash |
| `site.webmanifest` | PWA manifest |

> Note: 16×16 and 32×32 use a simplified design (just the "R") because the laurel
> wreath becomes muddy at tiny sizes. Apple touch and PWA icons use the full design.

## Install in Rankgim

### 1. Drop files into `public/`

```
public/
├── favicon.ico
├── favicon.svg
├── favicon-16x16.png
├── favicon-32x32.png
├── favicon-48x48.png
├── apple-touch-icon.png
├── android-chrome-192x192.png
├── android-chrome-512x512.png
└── site.webmanifest
```

Locally with Sail you can just `cp` them in — no artisan command needed.

### 2. Add `<link>` tags to `resources/views/partials/head.blade.php`

```blade
{{-- Favicon set --}}
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
<meta name="theme-color" content="#f5f0e1">
```

### 3. Hard-refresh

Browsers cache favicons aggressively. After deploy, hard-refresh
(`Ctrl+Shift+R` / `Cmd+Shift+R`) or open in incognito to see the change.

## Color reference

| Element | Hex | Notes |
|---|---|---|
| Background | `#f5f0e1` | Cream parchment |
| R + wreath outlines | `#3f2d1a` | Deep brown |
| Laurel leaves | `#5b8c3e` | Olive green |
