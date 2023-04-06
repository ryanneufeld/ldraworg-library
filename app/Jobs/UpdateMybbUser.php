<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\User;
use App\Models\MybbUser;

class UpdateMybbUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
      $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $mybb = MybbUser::find($this->user->forum_user_id);
      $mybb->username = $this->user->realname;
      $mybb->email = $this->user->email;
      $mybb->loginname = $this->user->name;
      $mybb_groups = empty($mybb->additionalgroups) ? [] : explode(',', $mybb->additionalgroups);
      foreach(config('ldraw.mybb-groups') as $role => $group) {
        if ($this->user->hasRole($role) && !in_array($group, $mybb_groups)) {
          $mybb_groups[] = $group;
        }
        elseif(!$this->user->hasRole($role) && in_array($group, $mybb_groups)) {
          $mybb_groups = array_values(array_filter($mybb_groups, fn ($m) => $m != $group));
        }
      }
      $mybb->additionalgroups = implode(',', $mybb_groups);
      $mybb->save();
    }
}
