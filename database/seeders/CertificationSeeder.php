<?php

namespace Database\Seeders;

use App\Domains\Certification\Models\Certification;
use App\Domains\Product\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CertificationSeeder extends Seeder
{
    public function run(): void
    {
        $globalCompliance = [
            ['name' => 'BSTI Certification', 'img' => 'bsti.png'],
            ['name' => 'GMP Certified', 'img' => 'gmp.png'],
            ['name' => 'ISO Standard', 'img' => 'iso.jpeg'],
            ['name' => 'Halal Certified', 'img' => 'halal.jpeg'],
            ['name' => 'HACCP Compliance', 'img' => 'haccp.png'],
        ];

        $qualityStandards = [
            ['name' => 'Pure & Natural', 'img' => 'pure.png'],
            ['name' => 'No MSG Added', 'img' => 'msg.png'],
            ['name' => 'Non-GMO Project', 'img' => 'gmo.png'],
            ['name' => 'Premium Quality', 'img' => 'premium.png'],
            ['name' => 'Halal Food Grade', 'img' => 'halal-food.png'],
        ];

        // 2. Process Global Compliance
        foreach ($globalCompliance as $index => $item) {
            $this->createAndAttachCert($item, 'Global Compliance', $index);
        }

        // 3. Process Quality Standards
        foreach ($qualityStandards as $index => $item) {
            $this->createAndAttachCert($item, 'Quality & Safety', $index);
        }
    }

    private function createAndAttachCert(array $item, string $category, int $order): void
    {
        $certification = Certification::create([
            'name'               => $item['name'],
            'category'           => $category,
            'organization'       => $this->getOrgName($item['name']),
            'given_date'         => now()->subMonths(rand(1, 24)),
            'expiry_date'        => now()->addYears(rand(1, 3)),
            'additional_details' => "Official {$item['name']} verified for production standards.",
            
            // Image paths updated to your requested format
            'logo_path'          => "certifications/" . $item['img'], 
            'image_path'         => "certifications/" . $item['img'], 
            
            'is_active'          => true,
            'sort_order'         => $order,
        ]);

        $randomProductIds = Product::inRandomOrder()->take(rand(2, 10))->pluck('id');
        $certification->products()->attach($randomProductIds);
    }

    /**
     * Helper to mock organization names based on certificate type
     */
    private function getOrgName(string $name): string
    {
        return match (true) {
            str_contains($name, 'BSTI')  => 'Bangladesh Standards and Testing Institution',
            str_contains($name, 'ISO')   => 'International Organization for Standardization',
            str_contains($name, 'Halal') => 'Global Halal Trust',
            str_contains($name, 'HACCP') => 'SGS International',
            default                      => 'Quality Assurance Board',
        };
    }
}