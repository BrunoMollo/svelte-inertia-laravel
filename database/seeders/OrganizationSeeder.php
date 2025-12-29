<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create activated organization
        Organization::factory()->activated()->create([
            'name' => 'Acme Corporation',
            'contact_name' => 'John Doe',
            'contact_email' => 'john.doe@acme.com',
            'contact_phone' => '+1-555-0100',
        ]);

        // Create blocked organization
        Organization::factory()->blocked()->create([
            'name' => 'Blocked Inc',
            'contact_name' => 'Jane Smith',
            'contact_email' => 'jane.smith@blocked.com',
            'contact_phone' => '+1-555-0200',
        ]);

        // Create another activated organization
        Organization::factory()->activated()->create([
            'name' => 'Tech Solutions Ltd',
            'contact_name' => 'Bob Johnson',
            'contact_email' => 'bob.johnson@techsolutions.com',
        ]);
    }
}
