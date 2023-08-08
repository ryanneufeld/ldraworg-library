<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserStoreRequest;
use App\Models\User;
use App\Models\MybbUser;
use Spatie\Permission\Models\Role;
use App\Jobs\UpdateMybbUser;
use App\Jobs\UserChangePartUpdate;

class UserController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::withCount('parts')->orderBy('realname')->get();

        return view('admin.users.index',compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(UserCreateRequest $request)
    {
        $validated = $request->validated();  
        $roles = Role::pluck('name','name')->all();
        $user = MybbUser::find($validated['forum_user_id']);
        return view('admin.users.create', ['roles' => $roles, 'user' => $user]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserStoreRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'realname' => $validated['realname'],
            'email' => $validated['email'],
            'password' => bcrypt(Str::random(40)),
            'forum_user_id' => $validated['forum_user_id'],
            'part_license_id' => $validated['part_license_id'],
        ]);
        $user->syncRoles($validated['roles']);
        $user->save();
        UpdateMybbUser::dispatch($user);
        return redirect()->
            route('admin.users.index')->
            with('success','User updated successfully');                        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();
        return view('admin.users.edit', compact('user','roles','userRole'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserStoreRequest $request, User $user)
    {
        $validated = $request->validated();
        $olddata = [];
        if ($validated['name'] != $user->name) {
            $olddata['name'] = $user->name;
        }
        if ($validated['realname'] != $user->realname) {
            $olddata['realname'] = $user->realname;
        }
        if ($validated['part_license_id'] != $user->part_license_id) {
            $olddata['part_license_id'] = $user->part_license_id;
        }
        $user->fill([
            'name' => $validated['name'],
            'realname' => $validated['realname'],
            'email' => $validated['email'],
            'part_license_id' => $validated['part_license_id'],
        ]);
        $user->syncRoles($validated['roles']);
        $user->save();
        if (!empty($olddata)) {
            UserChangePartUpdate::dispatch($user, $olddata);
        }
        UpdateMybbUser::dispatch($user);
        return redirect()->
            route('admin.users.index')->
            with('success','User updated successfully');                        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->
            route('users.index')->
            with('success','User deleted successfully');
    }
}
