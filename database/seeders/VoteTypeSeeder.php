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
      VoteType::create(['code' => 'C', 'short'=>'certify', 'name' => 'Certify', 'phrase' => 'Certify (Yes).  This file is ready for general release.', 'sort' => 4]);
      VoteType::create(['code' => 'A', 'short'=>'admincertify', 'name' => 'Admin Certify', 'phrase' => 'Admin Certify (Approve).  This file is approved for release.', 'sort' => 3]);
      VoteType::create(['code' => 'T', 'short'=>'fasttrack', 'name' => 'Admin Fast Track', 'phrase' => 'Fast-track (Yes).  This file is eligible for fast-track review and is approved release.', 'sort' => 6]);
      VoteType::create(['code' => 'H', 'short'=>'hold', 'name' => 'Hold', 'phrase' => 'Hold (No).  It\'s getting there, but not yet.', 'sort' => 5]);
      VoteType::create(['code' => 'N', 'short'=>'cancel', 'name' => 'Cancel Vote', 'phrase' => 'Cancel Vote.  This will clear your vote on this part.', 'sort' => 2]);
      VoteType::create(['code' => 'M', 'short'=>'comment', 'name' => 'Comment', 'phrase' => 'Comment.  Comment on this part without voting or changing your vote.', 'sort' => 1]);
    }
}
