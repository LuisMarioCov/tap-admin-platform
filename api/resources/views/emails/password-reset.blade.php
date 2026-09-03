<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambiar contraseña</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,sans-serif;color:#1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border-radius:8px;padding:32px;">
                    <tr>
                        <td>
                            <p style="margin:0 0 8px;font-size:13px;letter-spacing:.04em;color:#64748b;text-transform:uppercase;">TAP Admin</p>
                            <h1 style="margin:0 0 16px;font-size:22px;">¿Quieres cambiar tu contraseña?</h1>
                            <p style="margin:0 0 16px;line-height:1.5;">Hola {{ $userName }},</p>
                            <p style="margin:0 0 16px;line-height:1.5;">
                                Recibimos una solicitud para cambiar la contraseña de tu cuenta en TAP Admin.
                                Si fuiste tú, confirma con el botón. Si no reconoces esta solicitud, ignora este correo.
                            </p>
                            <p style="margin:24px 0;" align="center">
                                <a href="{{ $resetUrl }}" style="display:inline-block;background:#206bc4;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:6px;font-weight:bold;">
                                    Sí, cambiar mi contraseña
                                </a>
                            </p>
                            <p style="margin:0;font-size:13px;color:#64748b;line-height:1.5;">
                                El enlace caduca en 60 minutos. TAP nunca envía tu contraseña en el correo.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
