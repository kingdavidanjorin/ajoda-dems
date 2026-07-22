<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

final class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Sales',
                'code' => 'SALES',
                'description' => 'Property enquiries, reservations and sales processing.',
            ],
            [
                'name' => 'Accounts and Finance',
                'code' => 'ACCOUNTS',
                'description' => 'Payments, reconciliation, receipts and financial controls.',
            ],
            [
                'name' => 'Project Management',
                'code' => 'PROJECTS',
                'description' => 'Construction projects, milestones and progress reporting.',
            ],
            [
                'name' => 'Customer Service',
                'code' => 'CUSTOMER-SERVICE',
                'description' => 'Customer cases, enquiries, messaging and escalations.',
            ],
            [
                'name' => 'Property and Content',
                'code' => 'PROPERTY-CONTENT',
                'description' => 'Property catalogue, estate layouts and public content.',
            ],
            [
                'name' => 'Document Management',
                'code' => 'DOCUMENTS',
                'description' => 'Agreements, allocation letters and document workflows.',
            ],
            [
                'name' => 'System Administration',
                'code' => 'SYSTEM-ADMIN',
                'description' => 'System configuration, security and account administration.',
            ],
            [
                'name' => 'Executive Management',
                'code' => 'EXECUTIVE',
                'description' => 'Executive oversight, reporting and management decisions.',
            ],
        ];

        foreach ($departments as $department) {
            Department::query()->updateOrCreate(
                ['code' => $department['code']],
                [
                    ...$department,
                    'is_active' => true,
                ],
            );
        }
    }
}
