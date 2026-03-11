<?php

namespace App\Services;

use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class ReportExportService
{
    /**
     * Generate CSV from headers + rows and return a streamed response
     *
     * @param array $headers  e.g. ['Col A', 'Col B']
     * @param array $rows     array of arrays: [ ['a','b'], ['c','d'] ]
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function csv(array $headers, array $rows, string $filename = null)
    {
        $filename = $filename ?? 'report_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, $headers);

            foreach ($rows as $row) {
                // ensure scalar values
                $flat = array_map(function ($v) {
                    if (is_array($v) || is_object($v)) {
                        return json_encode($v, JSON_UNESCAPED_UNICODE);
                    }
                    return (string) $v;
                }, $row);
                fputcsv($out, $flat);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
        ]);
    }

    /**
     * Generate PDF from view / html (uses dompdf)
     *
     * @param string $html
     * @param string|null $filename
     * @return \Illuminate\Http\Response
     */
    public function pdf(string $html, string $filename = null)
    {
        $filename = $filename ?? 'report_' . now()->format('Ymd_His') . '.pdf';

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Build a simple HTML layout for PDF reports (header + summary + table)
     * $summary is key => value array; $columns = displayable header array; $rows = array of rows
     */
    public function buildPdfHtml(string $title, array $summary = [], array $columns = [], array $rows = [])
    {
        $logoUrl = ''; // optional: url to logo or base64 data
        $now = now()->format('d M Y H:i');

        $summaryHtml = '';
        if (!empty($summary)) {
            $summaryHtml .= '<div style="margin-bottom:12px;">';
            foreach ($summary as $k => $v) {
                $summaryHtml .= "<div style='font-size:12px;margin-bottom:4px;'><strong>{$k}:</strong> {$v}</div>";
            }
            $summaryHtml .= '</div>';
        }

        // Build table
        $table = "<table style='width:100%;border-collapse:collapse;font-size:12px;'>";
        // header
        $table .= "<thead><tr>";
        foreach ($columns as $col) {
            $table .= "<th style='border:1px solid #ddd;padding:8px;background:#f7f7f7;text-align:left;'>{$col}</th>";
        }
        $table .= "</tr></thead>";

        // body
        $table .= "<tbody>";
        foreach ($rows as $row) {
            $table .= "<tr>";
            foreach ($row as $cell) {
                $cellHtml = is_array($cell) || is_object($cell) ? htmlspecialchars(json_encode($cell, JSON_UNESCAPED_UNICODE)) : htmlspecialchars((string)$cell);
                $table .= "<td style='border:1px solid #ddd;padding:8px;vertical-align:top'>{$cellHtml}</td>";
            }
            $table .= "</tr>";
        }
        $table .= "</tbody></table>";

        $html = "
            <html>
            <head>
                <meta charset='utf-8' />
                <style>
                    body { font-family: DejaVu Sans, sans-serif; color: #222; }
                    .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
                    .title { font-size:20px; font-weight:700; }
                    .meta { font-size:12px; color:#666; }
                    .summary { margin-bottom:12px; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <div>
                        <div class='title'>{$title}</div>
                        <div class='meta'>Generated: {$now}</div>
                    </div>
                    <div>
                        <img src='{$logoUrl}' alt='' style='max-height:60px;'/>
                    </div>
                </div>

                <div class='summary'>{$summaryHtml}</div>

                <div class='table'>{$table}</div>
            </body>
            </html>
        ";

        return $html;
    }
}
