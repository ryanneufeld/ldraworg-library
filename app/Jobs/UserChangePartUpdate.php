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
    public array $olddata;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user, array $olddata)
    {
      $this->user = $user;
      $this->olddata = $olddata;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
      $parts = \App\Models\Part::where(function($query) {
        $query->orWhere('user_id', $this->user->id)->orWhereHas('history', function ($q) {
          $q->where('user_id', $this->user->id);
        });
      })->get();
      foreach($parts as $p) {
        if ($p->isUnofficial()) {
          return;
        }
        $oldLic = $p->part_license_id;
        $p->updateLicense();
        $md = $p->minor_edit_data;
        if (isset($this->olddata['name']) ||
            $p->part_license_id != $oldLic || 
            ($p->user_id == $this->user->id && isset($this->olddata['realname']))) {
          if ($p->part_license_id != $oldLic) {
            $md['license'] = \App\Models\PartLicense::find($oldLic)->name . " to " . $p->license->name;
          }
          if ($p->user_id == $this->user->id && isset($this->olddata['realname'])) {
            $md['realname'] = $this->olddata['realname'] . " to " . $this->user->realname;
          }
          if (isset($this->olddata['name'])) {
            $md['name'] = $this->olddata['name'] . " to " . $this->user->name;
          }
          $p->minor_edit_data = $md;
          $p->refreshHeader();
        }
      }
    }
}
