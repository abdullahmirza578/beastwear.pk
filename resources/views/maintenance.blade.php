<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $siteSettings->store_name }} — Back Soon</title>
    <style>
        body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0b0b0c;color:#fff;font:16px/1.6 system-ui,sans-serif;text-align:center}
        main{max-width:560px;padding:32px}
        h1{font-size:clamp(32px,7vw,56px);margin:0 0 12px}
        p{color:#c8c8cc}
    </style>
</head>
<body>
    <main>
        <h1>{{ $siteSettings->store_name }}</h1>
        <p>{{ $siteSettings->tagline }}</p>
        <p>We are making a few updates and will be back soon.</p>
        <p>For help, contact <a href="mailto:{{ $siteSettings->email }}" style="color:#fff">{{ $siteSettings->email }}</a>.</p>
    </main>
</body>
</html>
