<?php
/**
 * Author: Archie, Disono (webmonsph@gmail.com)
 * Website: http://www.webmons.com
 * Copyright 2016 Webmons Development Studio.
 * License: Apache 2.0
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * List data
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $content['title'] = app_title('Roles');
        $content['roles'] = Role::get();
        $content['request'] = $request;

        return admin_view('role.index', $content);
    }

    /**
     * Create new data
     *
     * @return mixed
     */
    public function create()
    {
        $content['title'] = app_title('Create Role');
        return admin_view('role.create', $content);
    }

    /**
     * Store new data
     *
     * @param Requests\Admin\RoleStore $request
     * @return mixed
     */
    public function store(Requests\Admin\RoleStore $request)
    {
        Role::store($request->all());

        return redirect('admin/roles');
    }

    /**
     * Edit data
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $content['title'] = app_title('Edit Role');
        $data = Role::single($id);
        if (!$data) {
            abort(404);
        }
        $content['role'] = $data;

        return admin_view('role.edit', $content);
    }

    /**
     * Update data
     *
     * @param Requests\Admin\RoleUpdate $request
     * @return mixed
     */
    public function update(Requests\Admin\RoleUpdate $request)
    {
        Role::edit($request->get('id'), $request->all());

        return redirect('admin/roles');
    }

    /**
     * Delete data
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        Role::remove($id);

        if (request()->ajax()) {
            return success_json_response('Successfully deleted role.');
        }

        return redirect()->back();
    }
}
