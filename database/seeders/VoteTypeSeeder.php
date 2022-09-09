<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VoteType;

class VoteTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      VoteType::create(['code' => 'C', 'short'=>'certify', 'name' => 'Certify', 'phrase' => 'Certify (Yes).  This file is ready for general release.']);
      VoteType::create(['code' => 'A', 'short'=>'admincertify', 'name' => 'Admin Certify', 'phrase' => 'Admin Certify (Approve).  This file is approved for release.']);
      VoteType::create(['code' => 'T', 'short'=>'fastrack', 'name' => 'Admin Fast Track', 'phrase' => 'Fast-track (Yes).  This file is eligible for fast-track review and is approved release.']);
      VoteType::create(['code' => 'H', 'short'=>'hold', 'name' => 'Hold', 'phrase' => 'Hold (No).  It\'s getting there, but not yet.']);
    }
}
