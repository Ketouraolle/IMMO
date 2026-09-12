<?php

namespace Database\Seeders;

use App\Models\Issue;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use App\Models\VisitRequest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Staff ---
        $admin = User::create([
            'name' => 'Ange Foka',
            'email' => 'admin@diasporaimmo.test',
            'password' => 'password',
            'phone' => '+237 6 90 00 00 01',
            'role' => 'admin',
        ]);

        // --- Owners ---
        $owner1 = User::create([
            'name' => 'Dr. Paul Nkemayang',
            'email' => 'paul.owner@diasporaimmo.test',
            'password' => 'password',
            'phone' => '+1 202 555 0101',
            'role' => 'owner',
        ]);

        $owner2 = User::create([
            'name' => 'Marie-Claire Fotso',
            'email' => 'marieclaire.owner@diasporaimmo.test',
            'password' => 'password',
            'phone' => '+33 6 12 34 56 78',
            'role' => 'owner',
        ]);

        // --- Tenants ---
        $tenant1 = User::create([
            'name' => 'Brice Mvondo',
            'email' => 'brice.tenant@diasporaimmo.test',
            'password' => 'password',
            'phone' => '+237 6 77 11 22 33',
            'role' => 'tenant',
        ]);

        $tenant2 = User::create([
            'name' => 'Stephanie Ateba',
            'email' => 'stephanie.tenant@diasporaimmo.test',
            'password' => 'password',
            'phone' => '+237 6 55 44 33 22',
            'role' => 'tenant',
        ]);

        // --- Properties ---
        $p1 = Property::create([
            'owner_id' => $owner1->id,
            'name' => 'Résidence Bonapriso A12',
            'address' => 'Rue Njo-Njo, Bonapriso',
            'city' => 'Douala',
            'type' => 'apartment',
            'monthly_rent' => 250000,
            'status' => 'occupied',
        ]);

        $p2 = Property::create([
            'owner_id' => $owner1->id,
            'name' => 'Villa Bastos B3',
            'address' => 'Avenue Bastos',
            'city' => 'Yaoundé',
            'type' => 'house',
            'monthly_rent' => 400000,
            'status' => 'vacant',
        ]);

        $p3 = Property::create([
            'owner_id' => $owner2->id,
            'name' => 'Studio Akwa Centre',
            'address' => 'Boulevard de la Liberté',
            'city' => 'Douala',
            'type' => 'studio',
            'monthly_rent' => 120000,
            'status' => 'occupied',
        ]);

        $p4 = Property::create([
            'owner_id' => $owner2->id,
            'name' => 'Appartement Odza D5',
            'address' => 'Carrefour Odza',
            'city' => 'Yaoundé',
            'type' => 'apartment',
            'monthly_rent' => 180000,
            'status' => 'vacant',
        ]);

        // --- Leases ---
        $lease1 = Lease::create([
            'property_id' => $p1->id,
            'tenant_id' => $tenant1->id,
            'start_date' => now()->subMonths(6),
            'rent_amount' => 250000,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $lease2 = Lease::create([
            'property_id' => $p3->id,
            'tenant_id' => $tenant2->id,
            'start_date' => now()->subMonths(3),
            'rent_amount' => 120000,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        // --- Payments (approved, admin-recorded) ---
        foreach ([2, 1, 0] as $monthsAgo) {
            Payment::create([
                'lease_id' => $lease1->id,
                'recorded_by' => $admin->id,
                'receipt_number' => Payment::generateReceiptNumber(),
                'amount' => 250000,
                'paid_on' => now()->subMonths($monthsAgo)->startOfMonth()->addDays(2),
                'period_covered' => now()->subMonths($monthsAgo)->format('F Y'),
                'method' => 'mobile_money',
                'status' => 'approved',
            ]);
        }

        Payment::create([
            'lease_id' => $lease2->id,
            'recorded_by' => $admin->id,
            'receipt_number' => Payment::generateReceiptNumber(),
            'amount' => 120000,
            'paid_on' => now()->subMonth()->startOfMonth()->addDays(1),
            'period_covered' => now()->subMonth()->format('F Y'),
            'method' => 'bank_transfer',
            'status' => 'approved',
        ]);

        // --- Payment awaiting admin validation (tenant-submitted) ---
        Payment::create([
            'lease_id' => $lease2->id,
            'submitted_by' => $tenant2->id,
            'amount' => 120000,
            'paid_on' => now(),
            'period_covered' => now()->format('F Y'),
            'method' => 'mobile_money',
            'status' => 'pending',
            'notes' => 'Paid via MoMo, ref 998211',
        ]);

        // --- Issues ---
        Issue::create([
            'property_id' => $p1->id,
            'reported_by' => $tenant1->id,
            'title' => 'Fuite d\'eau dans la salle de bain',
            'description' => 'Fuite constante sous le lavabo depuis 3 jours.',
            'priority' => 'high',
            'status' => 'open',
        ]);

        Issue::create([
            'property_id' => $p3->id,
            'reported_by' => $tenant2->id,
            'assigned_to' => $admin->id,
            'title' => 'Climatiseur en panne',
            'description' => 'Le climatiseur ne refroidit plus depuis le week-end.',
            'priority' => 'medium',
            'status' => 'in_progress',
        ]);

        // --- Visit requests (public leads) ---
        VisitRequest::create([
            'property_id' => $p2->id,
            'name' => 'Jean Ondoa',
            'email' => 'jean.ondoa@example.com',
            'phone' => '+237 6 99 88 77 66',
            'message' => 'Intéressé par une visite ce week-end.',
            'status' => 'new',
        ]);

        VisitRequest::create([
            'property_id' => $p4->id,
            'name' => 'Carine Belinga',
            'email' => 'carine.belinga@example.com',
            'phone' => '+237 6 22 33 44 55',
            'status' => 'new',
        ]);
    }
}
