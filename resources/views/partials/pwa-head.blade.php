{{--
    Everything the browser needs to consider this installable, in one place so
    the three layouts cannot drift apart on it.

    The apple-* tags are the iOS half: Safari ignores the manifest for
    "Add to Home Screen" and reads these instead, which is also why the
    install prompt has a separate iOS path.
--}}
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#C3073F">

<meta name="mobile-web-app-capable" content="yes">
<meta name="application-name" content="ICVault">

<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="ICVault">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
