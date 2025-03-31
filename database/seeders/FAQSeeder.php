<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FAQ;

class FAQSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        //
        FAQ::create([
            'question' => 'What materials are used to make CP Ateliers personalized jewelry?',
            'answer' => 'Our jewelry is crafted from high-quality materials, including sterling silver, gold-plated brass, and solid gold. Each piece is designed to be durable, elegant, and perfect for everyday wear.',
            'category' => 'About'
        ]);

        FAQ::create([
            'question' => 'How long does it take to create a personalized order?',
            'answer' => 'Personalized jewelry usually takes 3-7 business days to be handcrafted before shipping. Processing times may vary depending on the complexity of the engraving and order volume.',
            'category' => 'Orders'
        ]);



        FAQ::create([
            'question' => 'Shipping & Returns',
            'answer' => 'Yes, we ship worldwide! Shipping costs depend on your location and the selected shipping method. You can check the exact cost at checkout. Free shipping may be available on orders over a certain amount.',
            'category' => 'Shipping'
        ]);

        FAQ::create([
            'question' => 'How can I take care of my engraved jewelry to make it last longer?',
            'answer' => ' To maintain the beauty of your jewelry, avoid contact with water, perfume, and chemicals. Store it in a dry place and clean it regularly with a soft cloth. For sterling silver, a silver polishing cloth can help prevent tarnishing.',
            'category' => 'Care'
        ]);

    }
}
