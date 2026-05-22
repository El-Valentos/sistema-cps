<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada - CPS</title>
    <style>
        body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f3f4f6; }
        .container { text-align: center; padding: 2rem; }
        h1 { font-size: 6rem; color: #1e3a8a; margin: 0; }
        p { color: #6b7280; font-size: 1.2rem; }
        a { display: inline-block; margin-top: 1rem; padding: .75rem 1.5rem; background: #1e3a8a; color: white; border-radius: .5rem; text-decoration: none; }
        a:hover { background: #1e40af; }
    </style>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <p>La página que buscas no existe.</p>
        <a href="{{ url('/') }}">← Volver al inicio</a>
    </div>
</body>
</html>
