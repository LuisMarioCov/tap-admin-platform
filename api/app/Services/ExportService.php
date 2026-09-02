<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Profile;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class ExportService
{
    public function products(string $format): Response
    {
        $rows = Product::query()->orderBy('code')->get();

        return $this->buildTabularExport(
            'productos',
            ['Código', 'Nombre', 'Marca', 'Precio', 'Fecha'],
            $rows->map(fn (Product $p) => [
                $p->code,
                $p->name,
                $p->brand,
                (string) $p->price,
                $p->created_at?->format('Y-m-d H:i') ?? '',
            ])->all(),
            $format,
        );
    }

    public function profiles(string $format): Response
    {
        $rows = Profile::query()->orderBy('code')->get();

        return $this->buildTabularExport(
            'perfiles',
            ['Código', 'Nombre', 'Secciones', 'Fecha'],
            $rows->map(fn (Profile $p) => [
                $p->code,
                $p->name,
                implode(', ', $p->section_keys ?? []),
                $p->created_at?->format('Y-m-d H:i') ?? '',
            ])->all(),
            $format,
        );
    }

    public function users(string $format): Response
    {
        $rows = User::query()->orderBy('code')->get();

        return $this->buildTabularExport(
            'usuarios',
            ['Código', 'Nombre', 'Email', 'Teléfono', 'Fecha'],
            $rows->map(fn (User $u) => [
                $u->code,
                $u->name,
                $u->email,
                trim(($u->country_code ?? '').' '.($u->phone ?? '')),
                $u->created_at?->format('Y-m-d H:i') ?? '',
            ])->all(),
            $format,
        );
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string>>  $rows
     */
    private function buildTabularExport(string $title, array $headers, array $rows, string $format): Response
    {
        if ($format === 'xlsx') {
            return $this->xlsxResponse($title, $headers, $rows);
        }

        return $this->pdfResponse($title, $headers, $rows);
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string>>  $rows
     */
    private function xlsxResponse(string $title, array $headers, array $rows): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($title, 0, 31));

        foreach ($headers as $columnIndex => $header) {
            $sheet->setCellValue([$columnIndex + 1, 1], $header);
        }

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $sheet->setCellValue([$columnIndex + 1, $rowIndex + 2], $value);
            }
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'tap-xlsx-');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        $content = file_get_contents($tempFile) ?: '';
        unlink($tempFile);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$title.'.xlsx"',
        ]);
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string>>  $rows
     */
    private function pdfResponse(string $title, array $headers, array $rows): Response
    {
        $html = view('exports.table', [
            'title' => $title,
            'headers' => $headers,
            'rows' => $rows,
        ])->render();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);

            return $pdf->download($title.'.pdf');
        }

        return response($html, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$title.'.pdf"',
        ]);
    }
}
