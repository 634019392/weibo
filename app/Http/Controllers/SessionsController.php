<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    public  function __construct()
    {
        // 已登录用户不允许访问的页面(只让游客访问的)
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    // 用户登录页面
    public function create()
    {
        return view('sessions.create');
    }

    // 用户登录功能
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->has('remember'))) {
            // 登录成功后的相关操作
            session()->flash('success', '欢迎回来！');
            $fallback = route('users.show', [Auth::user()]);
            return redirect()->intended($fallback);
        } else {
            // 登录失败后的相关操作
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            return redirect()->back()->withInput(
                $request->except('password')
            );
        }

    }

    // 用户退出
    public function destroy()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect('login');
    }
}
