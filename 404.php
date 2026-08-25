<?php
// Página 404 genérica y neutra. No revela estructura ni archivos del sitio.
if (http_response_code() === 200) {
    http_response_code(404);
}
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Página no encontrada</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #cde5f4;
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
        }
        .box {
            max-width: 460px;
            width: 100%;
            background: #fff;
            border-radius: 8px;
            padding: 48px 34px;
            box-shadow: 0 6px 24px rgba(0,0,0,0.08);
            text-align: center;
        }
        h1 { font-size: 64px; color: #e06020; margin-bottom: 8px; }
        h2 { font-size: 20px; color: #222; font-weight: 500; margin-bottom: 12px; }
        p { font-size: 14px; color: #777; }
    </style>
</head>
<body>
    <div class="box">
        <h1>404</h1>
        <h2>Página no encontrada</h2>
        <p>La página que buscas no existe o no está disponible.</p>
    </div>
</body>
</html>
