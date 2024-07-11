<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;


class SocialiteController extends Controller
{


    public function redirectGoogle(): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        Log::info('redirect!!');
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle()
    {
        $authUser = auth()->user();
        $user = Socialite::driver('google')->user();
        $google_id = $user->getId();

        if ($authUser) {
            // ログイン済み時。
            // 他のユーザで入っている場合はエラーとする
            $check = User::where('google_id', $google_id)->whereNot('id', $authUser->id)->first();
            if ($check) {
                return redirect()->route('login')->with('status', 'used_other_user');
            }
            // google_id を設定して保存
            $authUser->google_id = $google_id;
            $authUser->save();

            // ログイン済みの場合は、紐づけを行ったプロフィール画面に戻す
            return redirect()->route('profile.edit');

        } else {
            // 未ログインから来た場合は、google_id があればそれでログインする
            $authUser = User::where('google_id', $google_id)->first();
            if ($authUser) {
                Auth::login($authUser);
                return redirect()->route('dashboard');
            }

            // メールアドレスが既に登録されている場合は、メールアドレスが登録されているとエラーにする
            $authUser = User::where('email', $user->email)->first();
            if ($authUser) {
                return redirect()->route('login')->with('status', 'email_already_exist');
            }

            // google_id, email ともに大丈夫ならば、ユーザを新規作成する
            $authUser = new User();
            $authUser->name = $user->getName();
            $authUser->email = $user->getEmail();
            $authUser->google_id = $google_id;
            $authUser->save();
            Auth::login($authUser);
            return redirect()->route('dashboard');
        }
    }
}
