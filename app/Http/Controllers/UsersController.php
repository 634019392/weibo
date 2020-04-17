<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        // 未登录用户不可以登录当前控制器的页面（除show，create，store外）
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index']
        ]);

        // 已登录用户不允许访问的页面(只让游客访问的)
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    // 用户列表页
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    // 删除管理员删除用户
    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    // 用户注册页面
    public function create()
    {
        return view('users.create');
    }

    // 用户创建功能
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:users|max:50',
            'email' => 'required|email|unique:users|max:225',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        Auth::login($user);
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show', $user);
    }

    // 展示个人信息页面
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    // 编辑个人信息页面
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    // 编辑个人信息功能
    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $data = ['name' => $request->name];
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        session()->flash('success', '个人资料更新成功');

        return redirect()->route('users.show', $user->id);
    }
}
