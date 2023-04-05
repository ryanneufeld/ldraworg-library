<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserChangePartUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
      $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
      $parts = \App\Models\Part::orWhere('user_id', $this->user->id)->orWhereHas('history', function ($q) {
        $q->where('user_id', $this->user->id);
      })->get();
      foreach($parts as $part) {
        $oldheader = $part->header;
        $part->updateLicense();
        $part->refreshHeader();
        if ($oldheader != $part->header) {
          $part->minor_edit_data['license'] = 'CC BY 2.0 to CC BY 4.0';
          $part->save();
        }
      }  
    }
}
