<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatorio de Usuarios</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4a90d9;
        }
        .header h1 {
            color: #4a90d9;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 9px;
        }
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px 10px;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .summary-item:last-child {
            border-right: none;
        }
        .summary-item .value {
            font-size: 22px;
            font-weight: bold;
            color: #4a90d9;
        }
        .summary-item .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            margin-top: 3px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h3 {
            font-size: 12px;
            margin-bottom: 10px;
            color: #333;
            padding-bottom: 5px;
            border-bottom: 1px solid #dee2e6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #4a90d9;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }
        td {
            padding: 6px 5px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .positions-grid {
            display: table;
            width: 100%;
        }
        .position-item {
            display: table-cell;
            padding: 8px;
            text-align: center;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .position-item .count {
            font-size: 16px;
            font-weight: bold;
            color: #4a90d9;
        }
        .position-item .name {
            font-size: 8px;
            color: #666;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 8px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Relatorio de Usuarios</h1>
            <p>Gerado em: {{ $generatedAt }}</p>
        </div>

        <div class="summary">
            <div class="summary-item">
                <div class="value">{{ $data['total'] ?? 0 }}</div>
                <div class="label">Total de Usuarios</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $data['active'] ?? 0 }}</div>
                <div class="label">Usuarios Ativos</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $data['inactive'] ?? 0 }}</div>
                <div class="label">Usuarios Inativos</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $data['users_with_goals'] ?? 0 }}</div>
                <div class="label">Com Metas</div>
            </div>
        </div>

        @if(count($data['by_position'] ?? []) > 0)
        <div class="section">
            <h3>Distribuicao por Cargo</h3>
            <div class="positions-grid">
                @foreach($data['by_position'] as $position)
                <div class="position-item">
                    <div class="count">{{ $position['count'] }}</div>
                    <div class="name">{{ $position['position'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="section">
            <h3>Lista de Usuarios</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 25%;">Nome</th>
                        <th style="width: 25%;">Email</th>
                        <th style="width: 15%;">Cargo</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 10%;">Metas</th>
                        <th style="width: 10%;">Concluidas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['users'] ?? [] as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->position ?? '-' }}</td>
                        <td>
                            <span class="status status-{{ $user->active ? 'active' : 'inactive' }}">
                                {{ $user->active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td>{{ $user->total_goals ?? 0 }}</td>
                        <td>{{ $user->goals_as_responsible_count ?? 0 }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">Nenhum usuario encontrado</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>Libertas Admin - Relatorio gerado automaticamente</p>
            <p>{{ $generatedAt }}</p>
        </div>
    </div>
</body>
</html>
