<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    public function __construct()
    {
        // 未登录用户不可以登录当前控制器的页面（除show，create，store, index外）
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
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

        $this->sendEmailConfirmationTo($user);

        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
    }

    // 展示个人信息页面
    public function show(User $user)
    {
        $statuses = $user->statuses()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('users.show', compact('user', 'statuses'));
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

    // 发送邮件激活
    public function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }

    // 用户激活页面
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->email_verified_at = now();
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }
}
