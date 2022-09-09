<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PartEventType;

class PartEventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      PartEventType::create(['slug' => 'review', 'name' => 'Review']);
      PartEventType::create(['slug' => 'submit', 'name' => 'Submit']);
      PartEventType::create(['slug' => 'edit', 'name' => 'Edit']);
      PartEventType::create(['slug' => 'rename', 'name' => 'Rename']);
      PartEventType::create(['slug' => 'release', 'name' => 'Release']);
      PartEventType::create(['slug' => 'delete', 'name' => 'Delete']);
      PartEventType::create(['slug' => 'comment', 'name' => 'Comment']);
   }
}
