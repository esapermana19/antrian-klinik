<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Queue;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $startDate = $request->query('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->toDateString());
        $doctorId = $request->query('doctor_id');

        // Query data kunjungan & antrean
        $queuesQuery = Queue::with(['patient', 'doctor', 'medicalRecord.invoice'])
            ->whereBetween('queue_date', [$startDate, $endDate])
            ->when($doctorId, function ($query, $doctorId) {
                $query->where('doctor_id', $doctorId);
            });

        $totalVisits = (clone $queuesQuery)->count();
        $completedVisits = (clone $queuesQuery)->where('queue_status', 'completed')->count();
        $canceledVisits = (clone $queuesQuery)->where('queue_status', 'cancel')->count();

        // Query pendapatan dari invoice
        $invoicesQuery = Invoice::whereHas('medicalRecord.queue', function ($q) use ($startDate, $endDate, $doctorId) {
            $q->whereBetween('queue_date', [$startDate, $endDate])
                ->when($doctorId, function ($dq, $doctorId) {
                    $dq->where('doctor_id', $doctorId);
                });
        });

        $totalRevenue = (clone $invoicesQuery)->where('payment_status', 'paid')->sum('total_amount');
        $examRevenue = (clone $invoicesQuery)->where('payment_status', 'paid')->sum('examination_fee');
        $medRevenue = (clone $invoicesQuery)->where('payment_status', 'paid')->sum('medication_fee');

        $records = $queuesQuery->latest('queue_date')
            ->orderBy('estimate_time', 'desc')
            ->paginate(15)
            ->withQueryString();

        $doctors = Doctor::where('is_active', true)->get();

        return Inertia::render('Reports/Index', [
            'records' => $records,
            'doctors' => $doctors,
            'summary' => [
                'total_visits' => $totalVisits,
                'completed_visits' => $completedVisits,
                'canceled_visits' => $canceledVisits,
                'total_revenue' => (float) $totalRevenue,
                'exam_revenue' => (float) $examRevenue,
                'med_revenue' => (float) $medRevenue,
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'doctor_id' => $doctorId,
            ],
        ]);
    }

    /**
     * Halaman cetak laporan bersih (Print View)
     */
    public function print(Request $request): Response
    {
        $startDate = $request->query('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->toDateString());
        $doctorId = $request->query('doctor_id');

        $queuesQuery = Queue::with(['patient', 'doctor', 'medicalRecord.invoice'])
            ->whereBetween('queue_date', [$startDate, $endDate])
            ->when($doctorId, function ($query, $doctorId) {
                $query->where('doctor_id', $doctorId);
            });

        $totalVisits = (clone $queuesQuery)->count();
        $completedVisits = (clone $queuesQuery)->where('queue_status', 'completed')->count();
        $canceledVisits = (clone $queuesQuery)->where('queue_status', 'cancel')->count();

        $invoicesQuery = Invoice::whereHas('medicalRecord.queue', function ($q) use ($startDate, $endDate, $doctorId) {
            $q->whereBetween('queue_date', [$startDate, $endDate])
                ->when($doctorId, function ($dq, $doctorId) {
                    $dq->where('doctor_id', $doctorId);
                });
        });

        $totalRevenue = (clone $invoicesQuery)->where('payment_status', 'paid')->sum('total_amount');
        $examRevenue = (clone $invoicesQuery)->where('payment_status', 'paid')->sum('examination_fee');
        $medRevenue = (clone $invoicesQuery)->where('payment_status', 'paid')->sum('medication_fee');

        $records = $queuesQuery->latest('queue_date')
            ->orderBy('estimate_time', 'desc')
            ->get();

        $doctor = $doctorId ? Doctor::find($doctorId) : null;

        return Inertia::render('Reports/Print', [
            'records' => $records,
            'summary' => [
                'total_visits' => $totalVisits,
                'completed_visits' => $completedVisits,
                'canceled_visits' => $canceledVisits,
                'total_revenue' => (float) $totalRevenue,
                'exam_revenue' => (float) $examRevenue,
                'med_revenue' => (float) $medRevenue,
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'doctor_id' => $doctorId,
                'doctor_name' => $doctor?->doctor_name,
                'specialization' => $doctor?->specialization,
            ],
            'clinic' => Setting::getClinicSettings(),
        ]);
    }

    /**
     * Export laporan ke file Excel berformat tabel resmi (.xlsx)
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $startDate = $request->query('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->toDateString());
        $doctorId = $request->query('doctor_id');

        $queues = Queue::with(['patient', 'doctor', 'medicalRecord.invoice'])
            ->whereBetween('queue_date', [$startDate, $endDate])
            ->when($doctorId, function ($query, $doctorId) {
                $query->where('doctor_id', $doctorId);
            })
            ->latest('queue_date')
            ->orderBy('estimate_time', 'desc')
            ->get();

        $clinicSettings = Setting::getClinicSettings();
        $clinicName = $clinicSettings['clinic_name'] ?? 'KLINIK PRATAMA';
        $clinicAddress = $clinicSettings['clinic_address'] ?? '';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Operasional');

        // Judul & Header Laporan
        $sheet->setCellValue('A1', strtoupper($clinicName));
        $sheet->setCellValue('A2', 'LAPORAN OPERASIONAL KUNJUNGAN & KEUANGAN KLINIK');
        $sheet->setCellValue('A3', 'Periode: '.Carbon::parse($startDate)->translatedFormat('d F Y').' s/d '.Carbon::parse($endDate)->translatedFormat('d F Y'));
        if ($clinicAddress) {
            $sheet->setCellValue('A4', $clinicAddress);
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('A4')->getFont()->setSize(9)->getColor()->setARGB('555555');

        // Header Tabel Kolom di Row 6
        $headerRow = 6;
        $headers = [
            'A' => ['No', 6],
            'B' => ['Tanggal', 14],
            'C' => ['No. Antrean', 14],
            'D' => ['Nama Pasien', 24],
            'E' => ['NIK', 20],
            'F' => ['Dokter / Poli', 24],
            'G' => ['Status Antrean', 16],
            'H' => ['Diagnosis', 26],
            'I' => ['Status Bayar', 16],
            'J' => ['Jasa Dokter (Rp)', 18],
            'K' => ['Biaya Obat (Rp)', 18],
            'L' => ['Total Biaya (Rp)', 20],
        ];

        foreach ($headers as $col => $info) {
            $sheet->setCellValue("{$col}{$headerRow}", $info[0]);
            $sheet->getColumnDimension($col)->setWidth($info[1]);
        }

        // Styling Header Tabel (Indigo Dark Theme dengan teks putih tebal)
        $sheet->getStyle("A{$headerRow}:L{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '312E81'], // Indigo 900
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // Isi Data Tabel
        $currentRow = $headerRow + 1;
        $totalExam = 0;
        $totalMed = 0;
        $totalAmount = 0;

        foreach ($queues as $index => $q) {
            $medrec = $q->medicalRecord;
            $invoice = $medrec?->invoice;

            $examFee = $invoice ? (float) $invoice->examination_fee : 0;
            $medFee = $invoice ? (float) $invoice->medication_fee : 0;
            $totalFee = $invoice ? (float) $invoice->total_amount : 0;

            if ($invoice && $invoice->payment_status === 'paid') {
                $totalExam += $examFee;
                $totalMed += $medFee;
                $totalAmount += $totalFee;
            }

            $sheet->setCellValue("A{$currentRow}", $index + 1);
            $sheet->setCellValue("B{$currentRow}", $q->queue_date ? Carbon::parse($q->queue_date)->format('d/m/Y') : '-');
            $sheet->setCellValue("C{$currentRow}", $q->queue_number);
            $sheet->setCellValue("D{$currentRow}", $q->patient?->patient_name ?? '-');
            $sheet->setCellValueExplicit("E{$currentRow}", $q->patient?->nik ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("F{$currentRow}", $q->doctor ? "{$q->doctor->doctor_name} ({$q->doctor->specialization})" : 'Dokter Umum');
            $sheet->setCellValue("G{$currentRow}", strtoupper($q->queue_status));
            $sheet->setCellValue("H{$currentRow}", $medrec?->diagnosis ?? '-');
            $sheet->setCellValue("I{$currentRow}", $invoice ? strtoupper($invoice->payment_status) : '-');
            $sheet->setCellValue("J{$currentRow}", $examFee);
            $sheet->setCellValue("K{$currentRow}", $medFee);
            $sheet->setCellValue("L{$currentRow}", $totalFee);

            // Alignment per kolom
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$currentRow}:L{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Format Angka Rupiah
            $sheet->getStyle("J{$currentRow}:L{$currentRow}")->getNumberFormat()->setFormatCode('#,##0');

            // Zebra striping baris genap
            if ($index % 2 === 1) {
                $sheet->getStyle("A{$currentRow}:L{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $currentRow++;
        }

        // Baris Total di paling bawah
        $summaryRow = $currentRow;
        $sheet->mergeCells("A{$summaryRow}:I{$summaryRow}");
        $sheet->setCellValue("A{$summaryRow}", 'TOTAL PENDAPATAN (LUNAS)');
        $sheet->setCellValue("J{$summaryRow}", $totalExam);
        $sheet->setCellValue("K{$summaryRow}", $totalMed);
        $sheet->setCellValue("L{$summaryRow}", $totalAmount);

        $sheet->getStyle("A{$summaryRow}:L{$summaryRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '0F172A'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("J{$summaryRow}:L{$summaryRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getRowDimension($summaryRow)->setRowHeight(24);

        // Border Tabel Lengkap
        $sheet->getStyle("A{$headerRow}:L{$summaryRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        $fileName = "Laporan_Klinik_{$startDate}_sd_{$endDate}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
