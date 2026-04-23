<?php

namespace Database\Seeders;

use App\Domains\Certification\Models\Certification;
use App\Domains\Product\Models\Product;
use Illuminate\Database\Seeder;

class CertificationSeeder extends Seeder
{
    public function run(): void
    {
        $globalCompliance = [
            ['name' => 'BSTI Certification', 'img' => 'bsti.png', 'logo_path' => 'bsti.png'],
            ['name' => 'GMP Certified', 'img' => 'gmp.jpg', 'logo_path' => 'gmp.png'],
            ['name' => 'ISO Standard', 'img' => 'iso.jpg', 'logo_path' => 'iso.png'],
            ['name' => 'Halal Certified', 'img' => 'halal.jpg', 'logo_path' => 'halal.png'],
            ['name' => 'HACCP Compliance', 'img' => 'haccp.jpg', 'logo_path' => 'haccp.png'],
        ];

        $qualityStandards = [
            ['name' => 'Pure & Natural', 'img' => 'pure.png', 'logo_path' => 'pure.png'],
            ['name' => 'No MSG Added', 'img' => 'msg.png', 'logo_path' => 'msg.png'],
            ['name' => 'Non-GMO Project', 'img' => 'gmo.png', 'logo_path' => 'gmo.png'],
            ['name' => 'Premium Quality', 'img' => 'premium.png', 'logo_path' => 'premium.png'],
            ['name' => 'Halal Food Grade', 'img' => 'halal-food.png', 'logo_path' => 'halal-food.png'],
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
            'image_path'    => "certifications/" . $item['img'],
            'logo_path'      => "certifications/" . $item['logo_path'],

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
