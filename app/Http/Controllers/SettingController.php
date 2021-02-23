<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\CheckUsers;
use App\Http\Requests\updatePass;

class SettingController extends Controller
{
    //
    public function index(){
        $auths = Auth::user();
        $myId = $auths->id;
        $hashPass = $auths->password;

        $query = DB::table('users')
                -> leftJoin('users_info', 'users.id', '=', 'users_info.id');
        $auth = $query->first();

        return view('setting.index', compact('auth'));
    }


    /**
    * パスワードの編集画面を表示します。
    */
    public function editPassword(){
        $auth = Auth::user();
        return view('setting.editPassword', compact('auth'));
    }


    /**
    * 電話番号の編集画面を表示します。
    */
    public function editTell(){
        $auth = Auth::user();
        return view('setting.editTell', compact('auth'));
    }


    /**
    * パスワードの更新を行います。
    */
    public function updatePassword(updatePass $request){
        $auths = Auth::user();
        $hashPass = $auths->password;

        // ハッシュ化済みパスワードのソルトを使って、受け取ったパスワードをハッシュ化後に比較
        if(!Hash::check($request->input('old_pass'), $hashPass)){
            session()->flash('msg_danger', 'パスワードが間違っています。');
            return back()->withInput();
        }elseif($request->input('new_pass1') <> $request->input('new_pass2')){ // 新しいパスワードが合っているか確認
            session()->flash('msg_danger', '新しいパスワードが確認用と一致していません。');
            return back()->withInput();
        }

        // dd( $request->input('new_pass1'), $request->input('new_pass2') );
        $auths->password = Hash::make($request->input('new_pass1'));
        $auths->save();
        session()->flash('msg_success', 'パスワードを更新しました');
        return redirect()->action('settingController@index');
    }


    /**
    * 電話番号の更新を行います。
    */
    public function updateTell(request $request){
        $auths = Auth::user();
        $hashPass = $auths->password;

        //

        // dd( $request->input('new_pass1'), $request->input('new_pass2') );
        // $auths->password = Hash::make($request->input('new_pass1'));
        // $auths->save();
        session()->flash('msg_success', '電話番号の更新プログラム作成中');
        return redirect()->action('settingController@index');

    }






}
