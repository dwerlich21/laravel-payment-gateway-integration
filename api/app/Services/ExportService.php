<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * Export goals to PDF
     */
    public function exportGoalsToPdf(array $data, array $filters = []): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView('reports.goals-pdf', [
            'data' => $data,
            'filters' => $filters,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('relatório-metas-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export goals to Excel
     */
    public function exportGoalsToExcel(array $data): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Metas');

        // Header styles
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4A90D9'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];

        // Headers
        $headers = ['ID', 'Descrição', 'Responsavel', 'Prazo', 'Peso (%)', 'Status', '% Atingido', 'Data Conclusão'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        // Data
        $row = 2;
        foreach ($data['goals'] ?? [] as $goal) {
            $sheet->setCellValue('A' . $row, $goal->id);
            $sheet->setCellValue('B' . $row, $goal->milestone_description);
            $sheet->setCellValue('C' . $row, $goal->responsible->name ?? 'N/A');
            $sheet->setCellValue('D' . $row, $goal->deadline ? $goal->deadline->format('d/m/Y') : '-');
            $sheet->setCellValue('E' . $row, $goal->weight);
            $sheet->setCellValue('F' . $row, $this->getStatusLabel($goal->status));
            $sheet->setCellValue('G' . $row, $goal->achievement_percentage ?? '-');
            $sheet->setCellValue('H' . $row, $goal->completion_date ? $goal->completion_date->format('d/m/Y') : '-');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Summary sheet
        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Resumo');

        $summarySheet->setCellValue('A1', 'Metrica');
        $summarySheet->setCellValue('B1', 'Valor');
        $summarySheet->getStyle('A1:B1')->applyFromArray($headerStyle);

        $summaryData = [
            ['Total de Metas', $data['total'] ?? 0],
            ['Peso Total', $data['total_weight'] ?? 0],
            ['Peso Medio', $data['avg_weight'] ?? 0],
            ['% Atingimento Medio', $data['avg_achievement'] ?? 0],
            ['Concluidas no Prazo', $data['completed_on_time'] ?? 0],
            ['Concluidas com Atraso', $data['completed_late'] ?? 0],
        ];

        $row = 2;
        foreach ($summaryData as $item) {
            $summarySheet->setCellValue('A' . $row, $item[0]);
            $summarySheet->setCellValue('B' . $row, $item[1]);
            $row++;
        }

        $summarySheet->getColumnDimension('A')->setAutoSize(true);
        $summarySheet->getColumnDimension('B')->setAutoSize(true);

        // Return as download
        $filename = 'relatório-metas-' . now()->format('Y-m-d') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export users to PDF
     */
    public function exportUsersToPdf(array $data): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView('reports.users-pdf', [
            'data' => $data,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('relatório-usuários-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export users to Excel
     */
    public function exportUsersToExcel(array $data): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Usuários');

        // Header styles
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4A90D9'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];

        // Headers
        $headers = ['ID', 'Nome', 'Email', 'Cargo', 'Status', 'Total Metas', 'Metas Concluidas'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Data
        $row = 2;
        foreach ($data['users'] ?? [] as $user) {
            $sheet->setCellValue('A' . $row, $user->id);
            $sheet->setCellValue('B' . $row, $user->name);
            $sheet->setCellValue('C' . $row, $user->email);
            $sheet->setCellValue('D' . $row, $user->position ?? '-');
            $sheet->setCellValue('E' . $row, $user->active ? 'Ativo' : 'Inativo');
            $sheet->setCellValue('F' . $row, $user->total_goals ?? 0);
            $sheet->setCellValue('G' . $row, $user->goals_as_responsible_count ?? 0);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Summary sheet
        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Resumo');

        $summarySheet->setCellValue('A1', 'Metrica');
        $summarySheet->setCellValue('B1', 'Valor');
        $summarySheet->getStyle('A1:B1')->applyFromArray($headerStyle);

        $summaryData = [
            ['Total de Usuários', $data['total'] ?? 0],
            ['Usuários Ativos', $data['active'] ?? 0],
            ['Usuários Inativos', $data['inactive'] ?? 0],
            ['Usuários com Metas', $data['users_with_goals'] ?? 0],
        ];

        $row = 2;
        foreach ($summaryData as $item) {
            $summarySheet->setCellValue('A' . $row, $item[0]);
            $summarySheet->setCellValue('B' . $row, $item[1]);
            $row++;
        }

        $summarySheet->getColumnDimension('A')->setAutoSize(true);
        $summarySheet->getColumnDimension('B')->setAutoSize(true);

        // Return as download
        $filename = 'relatório-usuários-' . now()->format('Y-m-d') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Get status label in Portuguese
     */
    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendente',
            'in_progress' => 'Em Andamento',
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
            default => $status,
        };
    }
}
