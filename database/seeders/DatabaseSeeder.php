<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Issue;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use App\Models\VisitRequest;
use App\Notifications\ContractReadyToSign;
use App\Notifications\VisitRequested;
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
            'visit_fee' => 0,
            'commission_rate' => 10,
            'status' => 'occupied',
        ]);

        $p2 = Property::create([
            'owner_id' => $owner1->id,
            'name' => 'Villa Bastos B3',
            'address' => 'Avenue Bastos',
            'city' => 'Yaoundé',
            'type' => 'house',
            'monthly_rent' => 400000,
            'visit_fee' => 5000,
            'commission_rate' => 10,
            'status' => 'vacant',
        ]);

        $p3 = Property::create([
            'owner_id' => $owner2->id,
            'name' => 'Studio Akwa Centre',
            'address' => 'Boulevard de la Liberté',
            'city' => 'Douala',
            'type' => 'studio',
            'monthly_rent' => 120000,
            'visit_fee' => 0,
            'commission_rate' => 12,
            'status' => 'occupied',
        ]);

        $p4 = Property::create([
            'owner_id' => $owner2->id,
            'name' => 'Appartement Odza D5',
            'address' => 'Carrefour Odza',
            'city' => 'Yaoundé',
            'type' => 'apartment',
            'monthly_rent' => 180000,
            'visit_fee' => 3000,
            'commission_rate' => 8,
            'status' => 'vacant',
        ]);

        // --- Visit dates on vacant properties (next 5 days, 09:00–16:00) ---
        foreach ([$p2, $p4] as $property) {
            foreach (range(1, 5) as $daysAhead) {
                $property->visitSlots()->create([
                    'date' => today()->addDays($daysAhead)->toDateString(),
                    'start_time' => '09:00',
                    'end_time' => '16:00',
                ]);
            }
        }

        // --- Leases ---
        // Start dates line up with the seeded payments: Brice is paid up, Stephanie owes the current month
        $lease1 = Lease::create([
            'property_id' => $p1->id,
            'tenant_id' => $tenant1->id,
            'start_date' => now()->subMonths(2)->startOfMonth(),
            'rent_amount' => 250000,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $lease2 = Lease::create([
            'property_id' => $p3->id,
            'tenant_id' => $tenant2->id,
            'start_date' => now()->subMonth()->startOfMonth(),
            'rent_amount' => 120000,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        // --- Payments (approved, admin-recorded; commission applied on approval) ---
        foreach ([2, 1, 0] as $monthsAgo) {
            Payment::create([
                'lease_id' => $lease1->id,
                'amount' => 250000,
                'paid_on' => now()->subMonths($monthsAgo)->startOfMonth()->addDays(2),
                'period_covered' => now()->subMonths($monthsAgo)->format('F Y'),
                'method' => 'orange_money',
                'status' => 'pending',
            ])->markApproved($admin);
        }

        Payment::create([
            'lease_id' => $lease2->id,
            'amount' => 120000,
            'paid_on' => now()->subMonth()->startOfMonth()->addDays(1),
            'period_covered' => now()->subMonth()->format('F Y'),
            'method' => 'bank_transfer',
            'status' => 'pending',
        ])->markApproved($admin);

        // --- Payment awaiting admin validation (tenant-submitted before mobile money checkout) ---
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

        // --- Contract sent to Brice, ready to sign ---
        $contract = Contract::create([
            'lease_id' => $lease1->id,
            'reference' => Contract::generateReference($lease1),
            'status' => 'draft',
            'created_by' => $admin->id,
            'special_conditions' => "Security deposit of two months' rent (500,000 XAF), refundable at the end of the lease.\nWater and electricity bills are paid by the tenant.",
        ]);
        $body = $contract->render();
        $contract->update(['body' => $body, 'body_hash' => hash('sha256', $body), 'status' => 'sent', 'sent_at' => now()]);
        $tenant1->notify(new ContractReadyToSign($contract));

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

        // --- Visit requests (booked from the public listings) ---
        $slotP2 = $p2->visitSlots()->orderBy('date')->first();
        $slotP4 = $p4->visitSlots()->orderBy('date')->skip(1)->first();

        $visits = [
            VisitRequest::create([
                'property_id' => $p2->id,
                'visit_slot_id' => $slotP2->id,
                'name' => 'Jean Ondoa',
                'email' => 'jean.ondoa@example.com',
                'phone' => '+237 6 99 88 77 66',
                'message' => 'Intéressé par une visite ce week-end.',
                'status' => 'new',
                'visit_date' => $slotP2->date,
                'visit_time' => '10:00',
                'fee_amount' => 5000,
                'payment_option' => 'pay_at_visit',
                'payment_status' => 'unpaid',
            ]),
            VisitRequest::create([
                'property_id' => $p4->id,
                'visit_slot_id' => $slotP4->id,
                'name' => 'Carine Belinga',
                'email' => 'carine.belinga@example.com',
                'phone' => '+237 6 22 33 44 55',
                'status' => 'new',
                'visit_date' => $slotP4->date,
                'visit_time' => '11:30',
                'fee_amount' => 3000,
                'payment_option' => 'pay_now',
                'payment_status' => 'paid',
                'payment_method' => 'orange_money',
                'transaction_ref' => 'OM-7Q2KD9XA',
                'paid_at' => now(),
            ]),
        ];

        foreach ($visits as $visit) {
            $admin->notify(new VisitRequested($visit));
        }
    }
}
