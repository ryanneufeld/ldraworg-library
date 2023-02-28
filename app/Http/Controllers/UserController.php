<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Jobs\UpdateMybbUser;

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
        $users = User::orderBy('realname')->get();

        return view('admin.users.index',compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
      $validated = $request->validate([
        'forum_user_id' => 'required|integer',
      ]);  
      $roles = Role::pluck('name','name')->all();
      $user = DB::connection('mybb')->table('mybb_users')
        ->select('uid', 'username', 'loginname', 'email')
        ->where('uid', $validated['forum_user_id'])->first();
      return view('admin.users.create', ['roles' => $roles, 'user' => $user]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
          'realname' => ['required', 'string', Rule::unique('users')],
          'name' => ['required', 'string', Rule::unique('users')],
          'email' => ['required', 'email', Rule::unique('users')],
          'roles' => 'required',
          'part_license_id' => 'required|exists:part_licenses,id'
        ]);

        $user->create([
          'name' => $validated['name'],
          'realname' => $validated['realname'],
          'email' => $validated['email'],
          'part_license_id' => $validated['part_license_id'],
        ]);
        $user->assignRole($validated['roles']);
        $user->syncRoles($validated['roles']);
        $user->save();
        UpdateMybbUser::dispatch($user);
        return redirect()->route('admin.users.index')
                        ->with('success','User updated successfully');                        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return view('users.show',compact('user'));
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
        return view('admin.users.edit',compact('user','roles','userRole'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
          'realname' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
          'name' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
          'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
          'roles' => 'required',
          'part_license_id' => 'required|exists:part_licenses,id'
        ]);

        $user->fill([
          'name' => $validated['name'],
          'realname' => $validated['realname'],
          'email' => $validated['email'],
          'part_license_id' => $validated['part_license_id'],
        ]);
        $user->assignRole($validated['roles']);
        $user->syncRoles($validated['roles']);
        $user->save();
        UpdateMybbUser::dispatch($user);
        return redirect()->route('admin.users.index')
                        ->with('success','User updated successfully');                        
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
        return redirect()->route('users.index')
                        ->with('success','User deleted successfully');
    }
}
