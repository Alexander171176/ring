<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'tailwind' => 'px-3 py-1',
                'title' => 'Контактная информация',
                'content' => 'Содержимое секции',
                'image' => ''
            ]
        ];

        DB::table('contacts')->insert($sections);
    }
}
