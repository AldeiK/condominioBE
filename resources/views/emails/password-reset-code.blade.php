<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperación de contraseña</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; padding:24px; color:#1f2937;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:12px; padding:24px; border:1px solid #e5e7eb;">
        <h2 style="margin-top:0; color:#111827;">Recuperación de contraseña</h2>
        <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta.</p>
        <p>Tu código de verificación es:</p>

        <div style="font-size:32px; font-weight:700; letter-spacing:8px; text-align:center; padding:16px; background:#eef2ff; border-radius:12px; color:#4338ca; margin:20px 0;">
            {{ $code }}
        </div>

        <p>Este código vence en 10 minutos.</p>
        <p>Si tú no solicitaste este cambio, puedes ignorar este correo.</p>

        <p style="margin-top:24px; color:#6b7280;">Condominio</p>
    </div>
</body>
</html>