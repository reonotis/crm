<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\EmailReset;
use App\Services\CheckUsers;
use App\Http\Requests\updatePass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use InterventionImage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Mail;



class SettingController extends Controller
{
    // 定数の設定
    private $_fileExtntion = ['jpg', 'jpeg'];
    private $_resize = '300';

    private $_user;                 //Auth::user()
    private $_auth_id ;             //Auth::user()->id;
    private $_auth_authority_id ;   //権限
    private $_toInfo ;
    private $_toReon ;
    private $_newEmail ;

    public function __construct(){
        $this->middleware(function ($request, $next) {
            $this->_user = \Auth::user();
            $this->_auth_id = $this->_user->id;
            $this->_auth_authority_id = $this->_user->authority_id;
            if($this->_auth_authority_id >= 8){
                session()->flash('msg_danger', '権限がありません');
                Auth::logout();
                return redirect()->intended('/');
            }
            $this->_toInfo = config('mail.toInfo');
            $this->_toReon = config('mail.toReon');
            return $next($request);
        });
    }


    /**
     * 設定画面を表示します。
     */
    public function index(){
        $auth = Auth::user();
        $myId = $auth->id;
        $query = DB::table('users')
                -> leftJoin('users_info', 'users.id', '=', 'users_info.id')
                ->where('users.id', $myId);
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
        $myId = $auth->id;

        $query = DB::table('users')
                -> leftJoin('users_info', 'users.id', '=', 'users_info.id');
        $auth = $query->first();
        return view('setting.editTell', compact('auth'));
    }

    /**
     * 住所の編集画面を表示します。
     */
    public function editAddress(){
        $auth = Auth::user();
        $myId = $auth->id;

        $query = DB::table('users')
                -> leftJoin('users_info', 'users.id', '=', 'users_info.id');
        $auth = $query->first();
        return view('setting.editAddress', compact('auth'));
    }

    /**
     * 画像の更新画面を表示します。
     */
    public function editImage(){
        $auth = Auth::user();
        $myId = $auth->id;

        $query = DB::table('users')
                ->leftJoin('users_info', 'users.id', '=', 'users_info.id')
                ->where('users.id', $this->_auth_id);
        $auth = $query->first();
        return view('setting.editImg', compact('auth'));
    }

    /**
     * メールアドレスの更新画面を表示します。
     */
    public function editEmail(){
        return view('setting.editEmail');
    }

    /**
     * メールアドレスの変更が可能か確認してユーザーにメールを送ります。
     */
    public function sendChangeEmailLink(Request $request){
        $new_email1 = $request->new_email1 ;
        $new_email2 = $request->new_email2 ;
        $this->_newEmail = $request->new_email1 ;

        // トークン生成
        $token = hash_hmac(
            'sha256',
            Str::random(40) . $new_email1,
            config('app.key')
        );

        // トークンをDBに保存
        DB::beginTransaction();
        try {
            if(!$new_email1) throw new \Exception("メールアドレスが入力されていません");
            if($new_email1 <> $new_email2) throw new \Exception("メールアドレスが確認用と一致していません");
            $param = [];
            $param['user_id'] = $this->_auth_id;
            $param['new_email'] = $new_email1;
            $param['token'] = $token;
            $email_reset = EmailReset::create($param);
            DB::commit();

            $data = [
                "instructor" => $this->_user->name,
                "url"        => url('').'/setting/resetEmail/'.$param['token']
            ];
            Mail::send('emails.changeEmail', $data, function($message){
                $message->to($this->_newEmail)
                ->subject('メールアドレス変更');
            });

            session()->flash('msg_success', '確認用のメールを新しいメールアドレスに送信しました。');
            return redirect('/home');
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('msg_danger','異常' );
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->back();    // 前の画面へ戻る
        }
        return view('setting.editEmail');
    }

    /**
     * メールアドレスの再設定処理
     *
     * @param Request $request
     * @param [type] $token
     */
    public function resetEmail($token){
        $email_resets = DB::table('email_resets')
            ->where('token', $token)
            ->first();

        // トークンが存在している、かつ、有効期限が切れていないかチェック
        if ($email_resets && !$this->tokenExpired($email_resets->created_at)) {

            // ユーザーのメールアドレスを更新
            $user = User::find($email_resets->user_id);
            $user->email = $email_resets->new_email;
            $user->save();

            // レコードを削除
            DB::table('email_resets')
                ->where('token', $token)
                ->delete();

                session()->flash('msg_success', 'メールアドレスを更新しました。');
                return redirect('/home');
        } else {
            // レコードが存在していた場合削除
            if ($email_resets) {
                DB::table('email_resets')
                    ->where('token', $token)
                    ->delete();
            }
            session()->flash('msg_danger','メールアドレスの更新に失敗しました。' );
            return redirect('/home');
        }
    }

    /**
     * トークンが有効期限切れかどうかチェック
     *
     * @param  string  $createdAt
     * @return bool
     */
    protected function tokenExpired($createdAt)
    {
        // トークンの有効期限は60分に設定
        $expires = 60 * 60;
        return Carbon::parse($createdAt)->addSeconds($expires)->isPast();
    }

    /**
     * パスワードの更新を行います。
     */
    public function updatePassword(updatePass $request){
        $auth = Auth::user();
        $hashPass = $auth->password;

        // ハッシュ化済みパスワードのソルトを使って、受け取ったパスワードをハッシュ化後に比較
        if(!Hash::check($request->input('old_pass'), $hashPass)){
            session()->flash('msg_danger', 'パスワードが間違っています。');
            return back()->withInput();
        }elseif($request->input('new_pass1') <> $request->input('new_pass2')){ // 新しいパスワードが合っているか確認
            session()->flash('msg_danger', '新しいパスワードが確認用と一致していません。');
            return back()->withInput();
        }

        // dd( $request->input('new_pass1'), $request->input('new_pass2') );
        $auth->password = Hash::make($request->input('new_pass1'));
        $auth->save();
        session()->flash('msg_success', 'パスワードを更新しました');
        return redirect()->action('SettingController@index');
    }

    /**
     * 電話番号の更新を行います。
     */
    public function updateTell(request $request){
        $newTell = $request->input('tell');

        //バリデーションcheck
        if(empty($newTell)){
            session()->flash('msg_danger', '電話番号を入力してください');
            return back();
        }
        if (preg_match('/\A\d{2,4}+-\d{2,4}+-\d{4}\z/', $newTell)) {
            $auths = Auth::user();
            $myId = $auths->id;
            DB::table('users_info')->updateOrInsert(
                ['id' => $myId],
                ['tel' =>  $newTell]
            );
            session()->flash('msg_success', '電話番号を更新しました');
            return redirect()->action('SettingController@index');
        }else{
            session()->flash('msg_danger', '入力規則に一致していません。');
            return back();
        }
    }

    /**
     * 住所の更新を行います。
     */
    public function updateAddress(request $request){
        $zip21 = $request->input('zip21');
        $zip22 = $request->input('zip22');
        $pref21 = $request->input('pref21');
        $addr21 = $request->input('addr21');
        $strt21 = $request->input('strt21');

        //バリデーションcheck
        if( strlen($zip21) <> 3 || strlen($zip22) <> 4 ){
            session()->flash('msg_danger', '郵便番号は3桁-4桁で入力してください');
            return back();
        }
        if( empty($pref21) || empty($addr21) || empty($strt21) ){
            session()->flash('msg_danger', '住所は全て入力してください');
            return back();
        }

        $auth = Auth::user();
        $myId = $auth->id;
        DB::table('users_info')->updateOrInsert(
            ['id' => $myId],
            [   'zip21'  =>  $zip21,
                'zip22'  =>  $zip22,
                'pref21' =>  $pref21,
                'addr21' =>  $addr21,
                'strt21' =>  $strt21,
            ]
        );
        session()->flash('msg_success', '住所を更新しました');
        return redirect()->action('SettingController@index');
    }


    /**
     * 画像の更新を行います。
     */
    public function updateImage(request $request){
        $auth = Auth::user();
        $myId = $auth->id;
        $file = $request->img;
        try {
            if(empty($file)) throw new \Exception("ファイルが指定されていません");
            // 登録可能な拡張子か確認して取得する
            $extension = $this->checkFileExtntion($file);

            // ファイル名の作成 => {日時} _ {ユーザーID(7桁に0埋め)} _ 'mainImg' . {拡張子}
            $BaseFileName =  date("ymd_His") . '_' . str_pad($myId, 7, 0, STR_PAD_LEFT) . '_' . 'mainImg' . $extension;

            // 画像サイズを横幅500の比率にして保存する。
            $this->makeImgFile($file, $BaseFileName);

            // DB更新
            DB::table('users_info')->updateOrInsert(
                ['id' => $myId],
                ['img_path'  =>  $BaseFileName,]
            );
            session()->flash('msg_success', '画像を更新しました');
            return redirect()->action('SettingController@index');

        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->back();    // 前の画面へ戻る
        }
    }

    /**
     * 渡されたファイルが登録可能な拡張子か確認するしてOKなら拡張子を返す
     */
    public function checkFileExtntion($file){
        // 渡された拡張子を取得
        $extension =  $file->extension();
        if(! in_array($extension, $this->_fileExtntion)){
            $fileExtntion = json_encode($this->_fileExtntion);
            throw new \Exception("登録できる画像の拡張子は". $fileExtntion ."のみです。");
        }
        return '.' . $file->extension();
    }

    /**
     * 渡された画像ファイルをリサイズして保存する
     */
    public function makeImgFile($file, $BaseFileName){
        if(empty($file)) throw new \Exception("ファイルがありません。");
        if(empty($BaseFileName)) throw new \Exception("ファイル名が決まっていません。");

        $image = InterventionImage::make($file)->exif();
        $width  = $image['COMPUTED']['Width'];
        $height = $image['COMPUTED']['Height'];
        if($width < $height){
            $length = $width;
        }else{
            $length = $height;
        }

        // 正方形の画像を作成
        $square_image = InterventionImage::make($file)
            ->crop($length, $length);

        // リサイズして保存
        $image = InterventionImage::make($square_image)
            ->resize($this->_resize, null, function ($constraint) {$constraint->aspectRatio();})
            ->save(public_path('../storage/app/public/mainImages/' . $BaseFileName ) );
    }


}
