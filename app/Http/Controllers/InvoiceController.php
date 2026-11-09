<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoice\PayInvoiceRequest;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Menampilkan daftar tagihan kasir
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $invoices = Invoice::with([
            'medicalRecord.patient',
            'medicalRecord.queue',
        ])
            ->when($status, function ($query, $status) {
                return $query->where('payment_status', $status);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('invoice_code', 'like', "%{$search}%")
                        ->orWhereHas('medicalRecord.patient', function ($p) use ($search) {
                            $p->where('patient_name', 'like', "%{$search}%")
                                ->orWhere('nik', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('invoice_id')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total_unpaid' => Invoice::where('payment_status', 'unpaid')->count(),
            'total_paid_today' => Invoice::where('payment_status', 'paid')
                ->whereDate('updated_at', today())
                ->count(),
            'revenue_today' => (float) Invoice::where('payment_status', 'paid')
                ->whereDate('updated_at', today())
                ->sum('total_amount'),
        ];

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Menampilkan detail invoice untuk cetak struk/nota
     */
    public function show(Invoice $invoice): Response
    {
        $invoice->load([
            'medicalRecord.patient',
            'medicalRecord.queue',
            'medicalRecord.medicationPrescriptions.medication',
        ]);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Memproses pelunasan tagihan di kasir
     */
    public function pay(PayInvoiceRequest $request, Invoice $invoice)
    {
        if ($invoice->payment_status === 'paid') {
            return back()->with('error', 'Tagihan ini sudah lunas sebelumnya.');
        }

        $validated = $request->validated();

        $invoice->update([
            'payment_status' => 'paid',
            'payment_method' => $validated['payment_method'],
        ]);

        return back()->with('success', "Tagihan {$invoice->invoice_code} berhasil diselesaikan.");
    }
}
