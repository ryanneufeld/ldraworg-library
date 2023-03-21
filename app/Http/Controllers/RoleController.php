<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use App\Http\Requests\RoleStoreRequest;

class RoleController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'roles');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      return view('admin.roles.index', ['roles' => Role::all()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
      return view('admin.roles.create', ['permissions' => Permission::orderBy('name')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleStoreRequest $request)
    {
      $validated = $request->validated();
      $role = Role::create(['name' => $validated['name']]);
      $role->syncPermissions($validated['permissions']);
      return redirect()->route('admin.roles.index')
                      ->with('success','Role created successfully');                        
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
      return view('admin.roles.edit', ['role' => $role, 'permissions' => Permission::orderBy('name')->get()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleStoreRequest $request, Role $role)
    {
      $validated = $request->validated();
      $role->syncPermissions($validated['permissions']);
      return redirect()->route('admin.roles.index')
                      ->with('success','Role updated successfully');                        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        //
    }
}
