<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\Administrator\AdministratorRequest;
use App\Http\Requests\Backend\Administrator\EditPasswordRequest;
use App\Http\Requests\Backend\Administrator\LoginRequest;
use App\Models\Administrator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdministratorController extends Controller
{

    protected $guard = 'administrator';

    public function __construct()
    {
        $this->middleware(
            'backend.login.check',
            [
                'except' => [
                    'showLoginForm', 'loginHandle',
                ]
            ]
        );
    }

    public function index()
    {
        $administrators = Administrator::orderByDesc('created_at')
                                        ->paginate(10);
        return view('backend.administrator.index', compact('administrators'));
    }

    public function showLoginForm()
    {
        return view('backend.auth.login');
    }

    public function loginHandle(LoginRequest $request)
    {
        if (
            ! Auth::guard($this->guard)->attempt(
                $request->only(['email', 'password'])
            )
        ) {
            flash('邮箱或密码错误');
            return back()->withInput(['email']);
        }
        return redirect('/');
    }

    public function create()
    {
        return view('backend.administrator.create');
    }

    public function store(
        AdministratorRequest $request,
        Administrator $administrator
    )
    {
        $administrator->fill($request->filldata())->save();
        flash('管理员添加成功', 'success');
        return back();
    }

    public function edit($id)
    {
        $administrator = Administrator::findOrFail($id);
        return view('backend.administrator.edit', compact('administrator'));
    }

    public function update(AdministratorRequest $request, $id)
    {
        $administrator = Administrator::findOrFail($id);
        $administrator->fill($request->filldata())->save();
        flash('管理员信息编辑成功', 'success');
        return back();
    }

    public function showEditPasswordForm()
    {
        return view('backend.auth.edit_password');
    }

    public function editPasswordHandle(EditPasswordRequest $request)
    {
        $administrator = Auth::guard($this->guard)->user();
        if (
            ! Hash::check(
                $request->input('old_password'),
                $administrator->password
            )
        ) {
            flash('原密码不正确');
            return back();
        }
        $administrator->fill($request->filldata())->save();
        flash('密码修改成功', 'success');
        return back();
    }

    public function destroy($id)
    {
        $administrator = Administrator::findOrFail($id);
        if (! $administrator->couldDestroy()) {
            flash('当前用户是超级管理员账户无法删除');
        } else {
            $administrator->delete();
            flash('管理员删除成功', 'success');
        }
        return back();
    }

    public function logoutHandle()
    {
        Auth::guard($this->guard)->logout();
        flash('成功退出', 'success');
        return redirect(route('backend.login'));
    }

}