<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->title }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4a90d9;
        }
        .header h1 {
            color: #4a90d9;
            margin: 0;
            font-size: 24px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .notification-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
        .type-goal_assigned {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .type-goal_deadline {
            background-color: #fff3e0;
            color: #f57c00;
        }
        .type-goal_completed {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        .message {
            font-size: 16px;
            margin-bottom: 25px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #4a90d9;
        }
        .details {
            margin-bottom: 25px;
        }
        .details-title {
            font-weight: 600;
            color: #666;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .details-item {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .details-item:last-child {
            border-bottom: none;
        }
        .details-label {
            font-weight: 500;
            color: #666;
        }
        .details-value {
            color: #333;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4a90d9;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
        }
        .button:hover {
            background-color: #357abd;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Libertas Admin</h1>
        </div>

        <p class="greeting">Olá, <strong>{{ $user->name }}</strong>!</p>

        <span class="notification-type type-{{ $notification->type }}">
            @switch($notification->type)
                @case('goal_assigned')
                    Nova Meta
                    @break
                @case('goal_deadline')
                    Prazo Próximo
                    @break
                @case('goal_completed')
                    Meta Concluída
                    @break
                @default
                    Notificação
            @endswitch
        </span>

        <div class="message">
            {{ $notification->message }}
        </div>

        @if($notification->data)
        <div class="details">
            <div class="details-title">Detalhes</div>
            @if(isset($notification->data['milestone_description']))
            <div class="details-item">
                <span class="details-label">Meta:</span>
                <span class="details-value">{{ $notification->data['milestone_description'] }}</span>
            </div>
            @endif
            @if(isset($notification->data['deadline']))
            <div class="details-item">
                <span class="details-label">Prazo:</span>
                <span class="details-value">{{ $notification->data['deadline'] }}</span>
            </div>
            @endif
            @if(isset($notification->data['weight']))
            <div class="details-item">
                <span class="details-label">Peso:</span>
                <span class="details-value">{{ $notification->data['weight'] }}%</span>
            </div>
            @endif
            @if(isset($notification->data['days_remaining']))
            <div class="details-item">
                <span class="details-label">Dias restantes:</span>
                <span class="details-value">{{ $notification->data['days_remaining'] }} dia(s)</span>
            </div>
            @endif
        </div>
        @endif

        <div style="text-align: center;">
            <a href="{{ config('app.frontend_url', config('app.url')) }}" class="button">
                Acessar Sistema
            </a>
        </div>

        <div class="footer">
            <p>Este é um email automático. Por favor, não responda.</p>
            <p>&copy; {{ date('Y') }} Libertas Admin. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
