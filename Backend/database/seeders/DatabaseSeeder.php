<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Receipt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create super admin user
        $superAdmin = User::create([
            'name' => 'Andrew McDowell',
            'email' => 'lockedincsoftware@gmail.com',
            'password' => Hash::make('LockedIn247@alltimes'),
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Create 1 org
        $org = Org::create([
            'name' => 'QuickReceipt Corp',
        ]);

        // Create 3 users with different roles
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@quickreceipt.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@quickreceipt.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $member = User::create([
            'name' => 'Member User',
            'email' => 'member@quickreceipt.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Add users to org
        OrgMember::create([
            'org_id' => $org->id,
            'user_id' => $admin->id,
            'global_role' => 'admin',
        ]);

        OrgMember::create([
            'org_id' => $org->id,
            'user_id' => $manager->id,
            'global_role' => 'member',
        ]);

        OrgMember::create([
            'org_id' => $org->id,
            'user_id' => $member->id,
            'global_role' => 'member',
        ]);

        // Create 2 teams
        $salesTeam = Team::create([
            'org_id' => $org->id,
            'name' => 'Sales Team',
        ]);

        $devTeam = Team::create([
            'org_id' => $org->id,
            'name' => 'Development Team',
        ]);

        // Add users to teams
        TeamMember::create([
            'team_id' => $salesTeam->id,
            'user_id' => $admin->id,
            'team_role' => 'admin',
        ]);

        TeamMember::create([
            'team_id' => $salesTeam->id,
            'user_id' => $manager->id,
            'team_role' => 'admin',
        ]);

        TeamMember::create([
            'team_id' => $devTeam->id,
            'user_id' => $admin->id,
            'team_role' => 'admin',
        ]);

        TeamMember::create([
            'team_id' => $devTeam->id,
            'user_id' => $member->id,
            'team_role' => 'member',
        ]);

        // Create current year budgets for each team
        $currentYear = date('Y');
        
        $salesBudget = Budget::create([
            'team_id' => $salesTeam->id,
            'year' => $currentYear,
            'total_limit_cents' => 5000000, // $50,000
            'status' => 'active',
        ]);

        $devBudget = Budget::create([
            'team_id' => $devTeam->id,
            'year' => $currentYear,
            'total_limit_cents' => 10000000, // $100,000
            'status' => 'active',
        ]);

        // Create categories for each budget
        $categories = ['Supplies', 'Events', 'Equipment'];
        
        foreach ($categories as $categoryName) {
            Category::create([
                'org_id' => $org->id,
                'budget_id' => $salesBudget->id,
                'name' => $categoryName,
            ]);

            Category::create([
                'org_id' => $org->id,
                'budget_id' => $devBudget->id,
                'name' => $categoryName,
            ]);
        }

        // Get categories for transactions
        $salesSupplies = Category::where('budget_id', $salesBudget->id)->where('name', 'Supplies')->first();
        $salesEvents = Category::where('budget_id', $salesBudget->id)->where('name', 'Events')->first();
        $devEquipment = Category::where('budget_id', $devBudget->id)->where('name', 'Equipment')->first();
        $devSupplies = Category::where('budget_id', $devBudget->id)->where('name', 'Supplies')->first();

        // Create sample transactions
        $transactions = [
            [
                'org_id' => $org->id,
                'team_id' => $salesTeam->id,
                'budget_id' => $salesBudget->id,
                'user_id' => $manager->id,
                'type' => 'expense',
                'amount_cents' => 25000, // $250
                'date' => now()->subDays(5),
                'vendor' => 'Office Depot',
                'memo' => 'Office supplies for Q1',
                'category_id' => $salesSupplies->id,
                'payment_type' => 'org_card',
                'lost_receipt' => false,
                'reference_code' => 'TXN-' . str_pad(1, 6, '0', STR_PAD_LEFT),
            ],
            [
                'org_id' => $org->id,
                'team_id' => $salesTeam->id,
                'budget_id' => $salesBudget->id,
                'user_id' => $manager->id,
                'type' => 'expense',
                'amount_cents' => 150000, // $1,500
                'date' => now()->subDays(3),
                'vendor' => 'Conference Center',
                'memo' => 'Q1 Sales Conference',
                'category_id' => $salesEvents->id,
                'payment_type' => 'org_card',
                'lost_receipt' => false,
                'reference_code' => 'TXN-' . str_pad(2, 6, '0', STR_PAD_LEFT),
            ],
            [
                'org_id' => $org->id,
                'team_id' => $devTeam->id,
                'budget_id' => $devBudget->id,
                'user_id' => $member->id,
                'type' => 'expense',
                'amount_cents' => 200000, // $2,000
                'date' => now()->subDays(2),
                'vendor' => 'Apple Store',
                'memo' => 'New MacBook for development',
                'category_id' => $devEquipment->id,
                'payment_type' => 'org_card',
                'lost_receipt' => false,
                'reference_code' => 'TXN-' . str_pad(3, 6, '0', STR_PAD_LEFT),
            ],
            [
                'org_id' => $org->id,
                'team_id' => $devTeam->id,
                'budget_id' => $devBudget->id,
                'user_id' => $admin->id,
                'type' => 'expense',
                'amount_cents' => 50000, // $500
                'date' => now()->subDay(),
                'vendor' => 'Amazon',
                'memo' => 'Development tools and software',
                'category_id' => $devSupplies->id,
                'payment_type' => 'org_card',
                'lost_receipt' => true,
                'reference_code' => 'TXN-' . str_pad(4, 6, '0', STR_PAD_LEFT),
            ],
        ];

        foreach ($transactions as $transactionData) {
            Transaction::create($transactionData);
        }

        // Create sample files first
        $file1 = \App\Models\File::create([
            'path' => 'receipts/office-depot-001.pdf',
            'name' => 'office-depot-receipt.pdf',
            'mimetype' => 'application/pdf',
            'size' => 245760,
            'disk' => 'public',
        ]);

        $file3 = \App\Models\File::create([
            'path' => 'receipts/apple-store-001.pdf',
            'name' => 'apple-store-receipt.pdf',
            'mimetype' => 'application/pdf',
            'size' => 189440,
            'disk' => 'public',
        ]);

        // Create sample receipts
        $receipt1 = Receipt::create([
            'budget_id' => 1, // TXN-000001 is budget 1
            'file_id' => $file1->id,
        ]);

        $receipt3 = Receipt::create([
            'budget_id' => 2, // TXN-000003 is budget 2
            'file_id' => $file3->id,
        ]);

        // Update transactions to link to receipts
        $transaction1 = Transaction::where('reference_code', 'TXN-000001')->first();
        $transaction3 = Transaction::where('reference_code', 'TXN-000003')->first();

        if ($transaction1) {
            $transaction1->update(['receipt_id' => $receipt1->id]);
        }

        if ($transaction3) {
            $transaction3->update(['receipt_id' => $receipt3->id]);
        }
    }
}
