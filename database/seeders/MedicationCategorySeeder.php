<?php

namespace Database\Seeders;

use App\Models\Medication;
use App\Models\MedicationCategory;
use Illuminate\Database\Seeder;

class MedicationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'category_code' => 'A',
                'category_name' => 'Analgesik & Antipiretik',
                'medications' => [
                    ['code' => 'OBT-001', 'name' => 'Paracetamol 500mg', 'stock' => 120, 'price' => 8000],
                    ['code' => 'OBT-002', 'name' => 'Ibuprofen 400mg', 'stock' => 80, 'price' => 12000],
                    ['code' => 'OBT-003', 'name' => 'Asam Mefenamat 500mg', 'stock' => 60, 'price' => 15000],
                ],
            ],
            [
                'category_code' => 'B',
                'category_name' => 'Antibiotik',
                'medications' => [
                    ['code' => 'OBT-004', 'name' => 'Amoxicillin 500mg', 'stock' => 90, 'price' => 15000],
                    ['code' => 'OBT-005', 'name' => 'Ciprofloxacin 500mg', 'stock' => 50, 'price' => 22000],
                    ['code' => 'OBT-006', 'name' => 'Cefixime 100mg', 'stock' => 40, 'price' => 35000],
                ],
            ],
            [
                'category_code' => 'C',
                'category_name' => 'Antihistamin & Alergi',
                'medications' => [
                    ['code' => 'OBT-007', 'name' => 'Cetirizine 10mg', 'stock' => 100, 'price' => 10000],
                    ['code' => 'OBT-008', 'name' => 'Loratadine 10mg', 'stock' => 75, 'price' => 12000],
                    ['code' => 'OBT-009', 'name' => 'Dexamethasone 0.5mg', 'stock' => 110, 'price' => 7000],
                ],
            ],
            [
                'category_code' => 'D',
                'category_name' => 'Lambung & Pencernaan',
                'medications' => [
                    ['code' => 'OBT-010', 'name' => 'Omeprazole 20mg', 'stock' => 85, 'price' => 18000],
                    ['code' => 'OBT-011', 'name' => 'Antasida Doen Tablet', 'stock' => 150, 'price' => 6000],
                    ['code' => 'OBT-012', 'name' => 'Lansoprazole 30mg', 'stock' => 65, 'price' => 25000],
                ],
            ],
            [
                'category_code' => 'E',
                'category_name' => 'Vitamin & Suplemen',
                'medications' => [
                    ['code' => 'OBT-013', 'name' => 'Vitamin C 500mg', 'stock' => 200, 'price' => 10000],
                    ['code' => 'OBT-014', 'name' => 'Vitamin B Complex', 'stock' => 120, 'price' => 9000],
                    ['code' => 'OBT-015', 'name' => 'Zinc 20mg Tablet', 'stock' => 95, 'price' => 11000],
                ],
            ],
        ];

        foreach ($categories as $catData) {
            $category = MedicationCategory::firstOrCreate(
                ['medication_category_code' => $catData['category_code']],
                ['medication_category_name' => $catData['category_name']]
            );

            foreach ($catData['medications'] as $med) {
                Medication::firstOrCreate(
                    ['medication_code' => $med['code']],
                    [
                        'medication_category_id' => $category->medication_category_id,
                        'medication_name' => $med['name'],
                        'stock' => $med['stock'],
                        'unit_price' => $med['price'],
                    ]
                );
            }
        }
    }
}
