<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatorio de Metas</title>
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
        .filters {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .filters h3 {
            font-size: 11px;
            margin-bottom: 5px;
            color: #666;
        }
        .filters span {
            display: inline-block;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 3px;
            margin-right: 5px;
            font-size: 9px;
        }
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-item {
            display: table-cell;
            width: 16.66%;
            text-align: center;
            padding: 10px 5px;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .summary-item:last-child {
            border-right: none;
        }
        .summary-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #4a90d9;
        }
        .summary-item .label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
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
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-in_progress { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 8px;
            color: #999;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Relatorio de Metas</h1>
            <p>Gerado em: {{ $generatedAt }}</p>
        </div>

        @if(count($filters) > 0)
        <div class="filters">
            <h3>Filtros Aplicados:</h3>
            @if(isset($filters['start_date']))
                <span>Data Inicio: {{ \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') }}</span>
            @endif
            @if(isset($filters['end_date']))
                <span>Data Fim: {{ \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') }}</span>
            @endif
            @if(isset($filters['status']))
                <span>Status: {{ $filters['status'] }}</span>
            @endif
        </div>
        @endif

        <div class="summary">
            <div class="summary-item">
                <div class="value">{{ $data['total'] ?? 0 }}</div>
                <div class="label">Total</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $data['total_weight'] ?? 0 }}%</div>
                <div class="label">Peso Total</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $data['avg_weight'] ?? 0 }}%</div>
                <div class="label">Peso Medio</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $data['avg_achievement'] ?? 0 }}%</div>
                <div class="label">% Medio Atingido</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $data['completed_on_time'] ?? 0 }}</div>
                <div class="label">No Prazo</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $data['completed_late'] ?? 0 }}</div>
                <div class="label">Atrasadas</div>
            </div>
        </div>

        <h3 style="font-size: 12px; margin-bottom: 10px; color: #333;">Detalhamento das Metas</h3>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 30%;">Descricao</th>
                    <th style="width: 15%;">Responsavel</th>
                    <th style="width: 10%;">Prazo</th>
                    <th style="width: 8%;">Peso</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 10%;">% Atingido</th>
                    <th style="width: 10%;">Conclusao</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['goals'] ?? [] as $goal)
                <tr>
                    <td>{{ $goal->id }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($goal->milestone_description, 50) }}</td>
                    <td>{{ $goal->responsible->name ?? 'N/A' }}</td>
                    <td>{{ $goal->deadline ? $goal->deadline->format('d/m/Y') : '-' }}</td>
                    <td>{{ $goal->weight }}%</td>
                    <td>
                        <span class="status status-{{ $goal->status }}">
                            @switch($goal->status)
                                @case('pending') Pendente @break
                                @case('in_progress') Em Andamento @break
                                @case('completed') Concluido @break
                                @case('cancelled') Cancelado @break
                                @default {{ $goal->status }}
                            @endswitch
                        </span>
                    </td>
                    <td>{{ $goal->achievement_percentage ?? '-' }}%</td>
                    <td>{{ $goal->completion_date ? $goal->completion_date->format('d/m/Y') : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">Nenhuma meta encontrada</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <p>Libertas Admin - Relatorio gerado automaticamente</p>
            <p>{{ $generatedAt }}</p>
        </div>
    </div>
</body>
</html>
