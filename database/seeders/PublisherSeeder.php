<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;

class PublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Publisher::insert([
            [
                'name' => 'Bentang Pustaka',
                'address' => 'Jl. Pandega Padma No. 19, Yogyakarta',
                'phone' => '0274-517373',
                'email' => 'info@bentangpustaka.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gramedia Pustaka Utama',
                'address' => 'Jl. Palmerah Barat No. 29-37, Jakarta',
                'phone' => '021-53650110',
                'email' => 'cs@gramediapustaka.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mizan Publishing',
                'address' => 'Jl. Cinambo No. 135, Bandung',
                'phone' => '022-7834310',
                'email' => 'info@mizan.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Republika Penerbit',
                'address' => 'Jl. Warung Buncit Raya No. 37, Jakarta',
                'phone' => '021-7803747',
                'email' => 'penerbit@republika.co.id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kepustakaan Populer Gramedia',
                'address' => 'Jl. Kompleks Gramedia, Jakarta',
                'phone' => '021-53650111',
                'email' => 'kpg@gramedia.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
