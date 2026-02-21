<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; border: 2px solid #e74c3c; border-radius: 8px; padding: 20px;">
    <h1 style="color: #e74c3c; margin-top: 0;">🚨 Alerta de Disco</h1>

    <p style="font-size: 16px;">O servidor <strong>{{ $server }}</strong> está com o disco em <strong style="color: #e74c3c;">{{ $usage }}</strong> de uso.</p>

    <p style="color: #666;">Ações recomendadas:</p>
    <ul style="color: #666;">
        <li>Limpar logs antigos</li>
        <li>Remover backups desnecessários</li>
        <li>Verificar cache do snapd</li>
        <li>Aumentar volume EBS se necessário</li>
    </ul>

    <p style="font-size: 12px; color: #999; margin-bottom: 0;">
        Alerta gerado em {{ now()->format('d/m/Y H:i:s') }}
    </p>
</div>
</body>
</html>
