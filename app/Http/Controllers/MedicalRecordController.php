<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalRecord\StoreMedicalRecordRequest;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Queue;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MedicalRecordController extends Controller
{
    /**
     * Menampilkan halaman form pemeriksaan dokter
     */
    public function create(Queue $queue)
    {
        // Pastikan relasi pasien dimuat
        $queue->load('patient');

        // Ambil daftar obat yang stoknya masih ada
        $medications = Medication::where('stock', '>', 0)
            ->orderBy('medication_name')
            ->get();

        return Inertia::render('MedicalRecords/Create', [
            'queue' => $queue,
            'patient' => $queue->patient,
            'medications' => $medications,
        ]);
    }

    /**
     * Menyimpan data rekam medis dan memotong stok obat
     */
    public function store(StoreMedicalRecordRequest $request, Queue $queue)
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated, $queue) {
                $patient = Patient::findOrFail($validated['patient_id']);

                // 1. Buat kode rekam medis unik (format: queue_number + patient NIK, max 26 karakter)
                $medrecCode = substr($queue->queue_number.$patient->nik, 0, 26);

                // 2. Simpan Data MedicalRecord
                $medrec = MedicalRecord::create([
                    'medrec_code' => $medrecCode,
                    'patient_id' => $validated['patient_id'],
                    'queue_id' => $queue->queue_id,
                    'main_complaint' => $validated['main_complaint'],
                    'blood_pressure' => ! empty($validated['blood_pressure']) ? $validated['blood_pressure'] : null,
                    'temperature' => ! empty($validated['temperature']) ? $validated['temperature'] : null,
                    'weight' => ! empty($validated['weight']) ? (int) $validated['weight'] : null,
                    'diagnosis' => $validated['diagnosis'],
                    'notes_on_treatment' => ! empty($validated['notes_on_treatment']) ? $validated['notes_on_treatment'] : null,
                ]);

                // 3. Loop resep obat (jika dokter memberikan resep)
                if (! empty($validated['prescriptions'])) {
                    foreach ($validated['prescriptions'] as $item) {
                        // Kunci baris obat ini agar tidak diubah transaksi lain bersamaan (Pessimistic Locking)
                        $medication = Medication::lockForUpdate()->findOrFail($item['medication_id']);

                        if ($medication->stock < $item['qty']) {
                            throw new \Exception("Stok obat {$medication->medication_name} tidak mencukupi! Sisa stok: {$medication->stock}");
                        }

                        // Simpan MedicationPrescription
                        $medrec->medicationPrescriptions()->create([
                            'medication_id' => $medication->medication_id,
                            'prescription_code' => 'RSP-'.uniqid(),
                            'qty' => $item['qty'],
                            'unit_price' => $medication->unit_price,
                            'total_price' => $medication->unit_price * $item['qty'],
                            'dosage' => $item['dosage'],
                        ]);

                        // Potong stok obat
                        $medication->decrement('stock', $item['qty']);
                    }
                }

                // 4. Ubah status antrean menjadi 'completed'
                $queue->update(['queue_status' => 'completed']);
            });

            return back()->with('success', 'Pemeriksaan selesai. Rekam medis dan resep berhasil disimpan.');

        } catch (\Exception $e) {
            // Jika terjadi error (misal stok kurang), transaksi otomatis di-rollback
            return back()->withErrors(['error_transaction' => $e->getMessage()]);
        }
    }
}
