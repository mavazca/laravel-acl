<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage permissions']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $permissions = Permission::all(); //Get all permissions

        return view('permissions.index')->with('permissions', $permissions);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::get(); //Get all roles

        return view('permissions.create')->with('roles', $roles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, ['name'=>'required|max:40',]);

        $name = $request['name'];
        $permission = new Permission();
        $permission->name = $name;
        $permission->save();

        $roles = $request['roles'];

        if (!empty($roles)) {
            foreach ($roles as $role) {
                $r = Role::where('id', '=', $role)->firstOrFail();

                $permission = Permission::where('name', '=', $name)->first();
                $r->givePermissionTo($permission);
            }
        }

        return redirect()->route('permissions.index')
            ->with(
                'success',
             'Permission'. $permission->name.' added!'
            );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return redirect('permissions');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        $roles = Role::all();

        return view('permissions.edit', compact('permission', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $this->validate($request, [
            'name'=>'required|max:40',
        ]);

        $name = $request['name'];
        $permission->name = $name;
        $permission->save();

        $roles = $request['roles'];

        $r_all = Role::all();
        foreach ($r_all as $r) {
            $permission->revokePermissionTo($r); //Remove all roles associated with permission
        }

        if (!empty($roles)) {
            foreach ($roles as $role) {
                $r = Role::where('id', '=', $role)->firstOrFail(); //Get corresponding form //role in db

                $permission = Permission::where('name', '=', $name)->first();

                $r->givePermissionTo($permission);  //Assign role to permission
            }
        }

        return redirect()->route('permissions.index')
            ->with(
                'success',
             'Permission'. $permission->name.' updated!'
            );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        //Make it impossible to delete this specific permission
        if ($permission->name == "manage users") {
            return redirect()->route('permissions.index')
            ->with(
                'warning',
                'Cannot delete this Permission!'
            );
        }
        //Make it impossible to delete this specific permission
        if ($permission->name == "manage roles") {
            return redirect()->route('permissions.index')
            ->with(
                'warning',
                'Cannot delete this Permission!'
            );
        }
        //Make it impossible to delete this specific permission
        if ($permission->name == "manage permissions") {
            return redirect()->route('permissions.index')
            ->with(
                'warning',
                'Cannot delete this Permission!'
            );
        }

        $permission->delete();

        return redirect()->route('permissions.index')
            ->with(
                'success',
             'Permission deleted!'
            );
    }
}
