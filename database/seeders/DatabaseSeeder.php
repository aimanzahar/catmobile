<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $services = [
            [
                'name' => 'Basic Grooming',
                'slug' => 'basic-grooming',
                'description' => 'Essential grooming package including bath, blow dry, nail trimming, ear cleaning, and light brushing. Perfect for regular maintenance of your cat\'s hygiene.',
                'price' => 30.00,
                'duration_minutes' => 45,
                'icon' => 'scissors',
                'sort_order' => 1,
            ],
            [
                'name' => 'Full Grooming',
                'slug' => 'full-grooming',
                'description' => 'Complete grooming experience with bath, full haircut & styling, nail trimming, ear cleaning, teeth brushing, and de-shedding treatment.',
                'price' => 60.00,
                'duration_minutes' => 90,
                'icon' => 'sparkles',
                'sort_order' => 2,
            ],
            [
                'name' => 'Spa Package',
                'slug' => 'spa-package',
                'description' => 'Premium pampering session featuring everything in Full Grooming plus aromatherapy bath, deep conditioning, paw massage, and premium shampoo treatment.',
                'price' => 90.00,
                'duration_minutes' => 120,
                'icon' => 'heart',
                'sort_order' => 3,
            ],
            [
                'name' => 'Pet Taxi',
                'slug' => 'pet-taxi',
                'description' => 'Convenient door-to-door pickup and drop-off service for your cat. Our trained drivers ensure safe and comfortable transportation.',
                'price' => 25.00,
                'duration_minutes' => 30,
                'icon' => 'truck',
                'sort_order' => 4,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['slug' => $service['slug']], $service);
        }
    }
}
