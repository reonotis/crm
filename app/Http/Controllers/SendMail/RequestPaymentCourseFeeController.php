<?php

namespace App\Http\Controllers\SendMail;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCourseMapping;
use App\Models\InstructorCourseSchedule;
use Illuminate\Http\Request;
use Mail;

class RequestPaymentCourseFeeController extends Controller
{
    private $_user;                 //Auth::user()
    private $_auth_id ;             //Auth::user()->id;
    private $_auth_authority_id ;   //権限
    private $_toAkemi ;
    private $_toInfo ;
    private $_toReon ;
    private $_toInstructor ;

    public function __construct(){
        $this->middleware(function ($request, $next) {
            $this->_user = \Auth::user();
            $this->_auth_id = $this->_user->id;
            $this->_auth_authority_id = $this->_user->authority_id;
            if($this->_auth_authority_id >= 8){
                dd("権限がありません。");
            }
            $this->_toAkemi = config('mail.toAkemi');
            $this->_toInfo = config('mail.toInfo');
            $this->_toReon = config('mail.toReon');
            return $next($request);
        });
    }

    /**
     *
     */
    public function index($id){
        $CCM = CustomerCourseMapping::select('customer_course_mapping.*', 'customers.name')
        ->join('customers','customers.id','customer_course_mapping.customer_id')
        ->find($id);
        $instructor_courses_id =$CCM->instructor_courses_id;

        $ICS = InstructorCourseSchedule::
        where('instructor_courses_id', $instructor_courses_id )
        ->orderBy('date', 'asc')
        ->first();

        // A 購入日から1週間後を調べる
        $oneWeekLater = date('Y-m-d', strtotime('1 week', strtotime($CCM->date)));

        // B コースの初回日時から3日前を調べる
        $threeDaysAGo = date('Y-m-d', strtotime('-3 day', strtotime($ICS->date)));

        // A or B の日付が近い方を期限日にする
        if($oneWeekLater > $threeDaysAGo){
            $dayLimit = $threeDaysAGo;
        }else{
            $dayLimit = $oneWeekLater;
        }
        $dayLimit = $oneWeekLater;

        return view('email_forms.requestPaymentCourseFee', ['CCM' => $CCM, 'dayLimit'=> $dayLimit]);
    }

    /**
     *
     */
    public function sendmailPaymentCourseFee(Request $request, $id){
        try {
            $dayLimit = $request->dayLimit;
            $str_dayLimit = date('Y年m月d日', strtotime($dayLimit));
            $text = $request->text;
            $text = str_replace("###limitDay###", $str_dayLimit, $text);
            $text = str_replace("###price###", number_format($request->price), $text);

            $CCM = CustomerCourseMapping::find($id);
            $customer = Customer::find($id);
            $this->_toInstructor = $customer->email;

            // 依頼メールの送信
            $data = [
                "text"  => $text,
            ];
            Mail::send('emails.mailtext', $data, function($message){
                $message->to($this->_toInstructor, 'Test')
                ->cc($this->_toAkemi)
                ->bcc($this->_toReon)
                ->subject('入金依頼メール');
            });

            // DB更新
            $CCM->limit_day = $dayLimit;
            $CCM->price     = $request->price;
            $CCM->status    = 1;
            $CCM->save();

            session()->flash('msg_success', 'メールを送信しました。');
        return redirect()->action('AdminController@unPayd');
        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->back();    // 前の画面へ戻る
        }
    }


}