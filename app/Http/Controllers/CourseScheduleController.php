<?php

namespace App\Http\Controllers;

use App\Models\ApprovalComments;
use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\CourseScheduleWhens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mail;

class CourseScheduleController extends Controller
{

    private $_user;                 //Auth::user()
    private $_auth_id ;             //Auth::user()->id;
    private $_auth_authority_id ;   //権限
    private $_toAkemi ;
    private $_toInfo ;
    private $_toReon ;

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
     *一覧表示(Read)
     */
    public function index(){
        // パラリンコースを取得
        $para_course_schedules = CourseSchedule::select('course_schedules.*','users.name','courses.course_name','course_schedule_whens.date as dataTime')
            ->where('course_schedules.delete_flag', NULL)
            ->where('course_schedules.course_id', '<>' , 6)
            ->where('course_schedules.instructor_id', $this->_auth_id )
            ->join('users', 'users.id', '=', 'course_schedules.instructor_id')
            ->join('courses', 'courses.id', '=', 'course_schedules.course_id')
            ->join('course_schedule_whens', 'course_schedule_whens.course_schedules_id', '=', 'course_schedules.id')
            ->get();
        if($para_course_schedules){
            $para_course_schedules = $this->getApprovalNames($para_course_schedules);
        }

        // 養成コースを取得
        $subQuery = CourseScheduleWhens::whereIn('date', function($query) {
            $query->select(DB::raw('min(date) As date'))->from('course_schedule_whens')->groupBy('course_schedules_id')->where( 'course_schedule_whens.date', '>=' ,date('Y-m-d H:i:s'));
        });
        // サブクエリをJOINします
        $intr_course_schedules = CourseSchedule::select('course_schedules.*', 'course_schedule_whens.date', 'course_schedule_whens.howMany' )
        ->joinSub($subQuery, 'course_schedule_whens', function ($join) {
            $join->on('course_schedule_whens.course_schedules_id', '=', 'course_schedules.id');
        })
        ->where('course_schedules.course_id', 6)
        ->where('course_schedules.delete_flag', NULL)
        ->where('course_schedules.instructor_id', $this->_auth_id )
        ->orderBy('course_schedule_whens.date','asc')
        ->get();

        // 養成コースを取得course_name
        if($intr_course_schedules){
            $intr_course_schedules = $this->getApprovalNames($intr_course_schedules);
        }
        return view('course_schedule.index', compact('para_course_schedules', 'intr_course_schedules'));
    }

    /**
     *承認状態を確認して承認名を付与する
     */
    public function getApprovalNames($datas){
        if(empty($datas))throw new \Exception("コースが取得できていません。");
        foreach($datas as $data ){
            $this->getApprovalName($data);
        }
        return $datas;
    }

    /**
     *承認状態を確認して承認名を付与する
     */
    public function getApprovalName($data){
        if(empty($data))throw new \Exception("コースが取得できていません。");
            switch ($data->approval_flg) {
                case '0':
                    $data->approval_name = '未申請';
                    break;
                case '1':
                    $data->approval_name = '差し戻し';
                    break;
                case '2':
                    $data->approval_name = '申請中';
                    break;
                case '5':
                    $data->approval_name = '受理済み';
                    break;
                default:
                    $data->approval_name = '--';
                    break;
            }
        return $data;
    }

    /**
     *パラリンビクス講座申請画面のフォーム表示(Create)
     */
    public function paraCreate (){
        // コース一覧を取得する
        $coursesQuery = Course::query()
            -> where('courses.delete_flag','=','0')
            -> where('courses.parent_id','=','1');
        $courses = $coursesQuery -> get();
        return view('course_schedule.paraCreate', compact( 'courses'));
    }

    /**
     *新規作成のためのフォーム表示(Create)
     */
    public function intrCreate(){
        return view('course_schedule.intrCreate');
    }

    /**
     *パラリンビクス講座スケジュール申請確認画面
     *
     */
    public function paraConfilm(Request $request){
        try{
            $courses = Course::find($request->course_id);
            return view('course_schedule.paraConfilm', ['request' => $request, 'courses' => $courses]);
        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->action('CourseScheduleController@index');
        }
    }

    /**
     *イントラ養成コースの登録確認画面
     */
    public function intrConfilm(Request $request){
        try{
            return view('course_schedule.intrConfilm', ['request' => $request ]);
        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return view('course_schedule.intrCreate');
        }
    }

    /**
     * パラリンビクス講座を登録する
     */
    public function paraStore(Request $request){
        try {
            $CS = new CourseSchedule;
            $CS->instructor_id  = $this->_auth_id ;
            $CS->course_id      = $request->course_id ;
            $CS->erea     = $request->erea ;
            $CS->venue    = $request->venue ;
            $CS->price    = $request->price ;
            $CS->notices  = $request->notices ;
            $CS->comment  = $request->comment ;
            $CS->approval_flg    = 2 ;
            $CS->open_start_day = date( 'Y-m-d H:i:s', strtotime( $request->open_start_day ) )   ;
            $CS->open_finish_day = date( 'Y-m-d H:i:s', strtotime( $request->open_finish_day ) )   ;
            $CS->save();
            $CS_id = $CS->id;

            $CSW = new CourseScheduleWhens;
            $CSW->course_schedules_id = $CS_id;
            $CSW->instructor_id = $this->_auth_id;
            $CSW->date = date( 'Y-m-d H:i:s', strtotime( substr($request->date, 0, 10)."T".$request->time ) )  ;
            $CSW->howMany = 1;
            $CSW->save();

            $course = Course::find($CS->course_id);
            $data = [
                "instructor" => $this->_user->name,
                "course"     => $course->course_name,
                "url"        => url('').'/approval/index'
            ];
            Mail::send('emails.applicationAccepted', $data, function($message){
                $message->to($this->_toInfo, 'Test')
                ->cc($this->_toAkemi)
                ->bcc($this->_toReon)
                ->subject('事務局にスケジュールの申請がありました');
            });

            session()->flash('msg_success', '申請が完了しました');

        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->back();    // 前の画面へ戻る
        }
        return redirect()->action('CourseScheduleController@index');
    }

    /**
     *イントラ養成コースの登録
     */
    public function intrStore(Request $request){
        try{
            $CS = new CourseSchedule;
            $CS->instructor_id  = $this->_auth_id ;
            $CS->course_id      = 6 ;
            $CS->course_title   = $request->course_title ;
            $CS->erea     = $request->erea ;
            $CS->venue    = $request->venue ;
            $CS->price    = $request->price ;
            $CS->notices  = $request->notices ;
            $CS->comment  = $request->comment ;
            $CS->approval_flg    = 2 ;
            $CS->open_start_day = date( 'Y-m-d H:i:s', strtotime( $request->open_start_day ) )   ;
            $CS->open_finish_day = date( 'Y-m-d H:i:s', strtotime( $request->open_finish_day ) )   ;
            $CS->save();
            $CS_id = $CS->id;

            // 日程の登録
            $this->storeCourseScheduleWhens($CS_id, $request->date1, 1);
            $this->storeCourseScheduleWhens($CS_id, $request->date2, 2);
            $this->storeCourseScheduleWhens($CS_id, $request->date3, 3);
            $this->storeCourseScheduleWhens($CS_id, $request->date4, 4);
            $this->storeCourseScheduleWhens($CS_id, $request->date5, 5);
            if($request->date6) $this->storeCourseScheduleWhens($CS_id, $request->date6, 6);
            if($request->date7) $this->storeCourseScheduleWhens($CS_id, $request->date7, 7);
            if($request->date8) $this->storeCourseScheduleWhens($CS_id, $request->date8, 8);
            if($request->date9) $this->storeCourseScheduleWhens($CS_id, $request->date9, 9);
            if($request->date10) $this->storeCourseScheduleWhens($CS_id, $request->date10, 10);

            $course = Course::find(6);
            $data = [
                "instructor" => $this->_user->name,
                "course"     => $course->course_name,
                "url"        => url('').'/approval/index'
            ];
            Mail::send('emails.applicationAccepted', $data, function($message){
                $message->to($this->_toInfo, 'Test')
                ->cc($this->_toAkemi)
                ->bcc($this->_toReon)
                ->subject('事務局にスケジュールの申請がありました');
            });

            session()->flash('msg_success', '申請が完了しました');
            return redirect()->action('CourseScheduleController@index');
        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->action('CourseScheduleController@index');
        }
    }

    /**
     * コースの日程を登録
     */
    public function storeCourseScheduleWhens($CS_id, $dateTime, $howMany){
        $CSW = new CourseScheduleWhens;
        $CSW->course_schedules_id = $CS_id;
        $CSW->instructor_id = $this->_auth_id;
        $CSW->date = substr($dateTime, 0, 10)." ".substr($dateTime, 11, 5).":00" ;
        $CSW->howMany = $howMany ;
        $CSW->save();

    }

    /**
     * show	パラリンコース1件の詳細表示(Read)
     */
    public function paraShow($id){
        try {
            // パラリンコースを取得
            $para_course = CourseSchedule::select('course_schedules.*','courses.course_name')
            ->join('courses','courses.id','=','course_schedules.course_id')
            ->find($id);

            // ユーザーのScheduleじゃなければ前の画面へ戻る
            if($para_course->instructor_id <> $this->_auth_id )throw new \Exception("あなたのスケジュールではありません");
            if($para_course->course_id == 6 )throw new \Exception("表示しようとしているスケジュールはパラリンビクス講座ではありません");

            $courseScheduleWhens = CourseScheduleWhens::where('course_schedules_id', $id )->get();

            $para_course = $this->getApprovalName($para_course);
            $ApprovalComments = ApprovalComments::where('course_schedules_id', $id )->get();
            return view('course_schedule.paraShow', ['para_course' => $para_course, 'courseScheduleWhens' => $courseScheduleWhens, 'ApprovalComments'=> $ApprovalComments]);
        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->back();    // 前の画面へ戻る
        }
    }

    /**
     * show	養成コース1件の詳細表示
     */
    public function intrShow($id){
        try {
            // 養成コースを取得
            $intr_course = CourseSchedule::select('course_schedules.*', 'courses.course_name')
            ->join('courses','courses.id','=','course_schedules.course_id')
            ->find($id);

            // ユーザーのScheduleじゃなければ前の画面へ戻る
            if($intr_course->instructor_id <> $this->_auth_id )throw new \Exception("あなたのスケジュールではありません");
            if($intr_course->course_id <> 6 )throw new \Exception("表示しようとしているスケジュールは養成講座ではありません");
            $intr_course = $this->getApprovalName($intr_course);

            $courseScheduleWhens = CourseScheduleWhens::where('course_schedules_id', $id )->orderBy('howMany')->get();
            $ApprovalComments = ApprovalComments::where('course_schedules_id', $id )->get();
            return view('course_schedule.intrShow', ['intr_course' => $intr_course, 'courseScheduleWhens' => $courseScheduleWhens, 'ApprovalComments' => $ApprovalComments ]);
        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->back();    // 前の画面へ戻る
        }
    }

    /**
     * show	パラリンビクスコースの編集画面表示
     */
    public function paraEdit($id){
        try {
            $intr_course = CourseSchedule::find($id);
            if($intr_course->instructor_id <> $this->_auth_id )throw new \Exception("あなたのスケジュールではありません");
            $courses = Course::where('parent_id',1)->get();

            $courseScheduleWhens = CourseScheduleWhens::where('course_schedules_id', $id )->get();

            // 受理済みかの確認
            if($intr_course->approval_flg == 5 ){
                return view('course_schedule.paraEditReleaseSchedule', ['intr_course' => $intr_course, 'courseScheduleWhens' => $courseScheduleWhens, 'courses' => $courses]);
            }
            return view('course_schedule.paraEdit', ['intr_course' => $intr_course, 'courseScheduleWhens' => $courseScheduleWhens, 'courses' => $courses]);
        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->back();    // 前の画面へ戻る
        }
    }

    /**
     * show	養成コースの編集画面表示
     */
    public function intrEdit($id){
        try {
            $auth_id = Auth::user()->id;
            $intr_course = CourseSchedule::find($id);
            $courseScheduleWhens = CourseScheduleWhens::where('course_schedules_id', $id )->orderBy('howMany')->get();
            // 受理済みかの確認
            if($intr_course->approval_flg == 5 ){
                return view('course_schedule.intrEditReleaseSchedule', ['intr_course' => $intr_course,'courseScheduleWhens' => $courseScheduleWhens]);
            }

            return view('course_schedule.intrEdit', ['intr_course' => $intr_course, 'courseScheduleWhens' => $courseScheduleWhens]);
        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->back();    // 前の画面へ戻る
        }
    }

    /**
     * update	パラリンビクスコースの更新
     */
    public function paraUpdate(Request $request, $id){
        try {
            $auth_id = Auth::user()->id;
            $intr_course = CourseSchedule::find($id);
            // 自分のスケジュールを更新しようとしているか確認
            if($intr_course->instructor_id <> $auth_id)throw new \Exception("不正な更新accessです");
            // 受理済みか削除されたデータじゃないか確認
            if($intr_course->approval_flg >= 5 || $intr_course->delete_flag == 1 )throw new \Exception("このデータは更新できません");
            $intr_course->course_id= $request->course_id ;
            $intr_course->price= $request->price ;
            $intr_course->erea= $request->erea ;
            $intr_course->venue= $request->venue ;
            $intr_course->notices= $request->notices ;
            $intr_course->comment= $request->comment ;
            $intr_course->approval_flg= 2 ;
            $intr_course->open_start_day=  date("Y-m-d H:i:00", strtotime($request->open_start_day));
            $intr_course->open_finish_day=  date("Y-m-d H:i:00", strtotime($request->open_finish_day));
            $intr_course->save();

            $courseScheduleWhens = CourseScheduleWhens::where('course_schedules_id', $id )->first();
            $courseScheduleWhens->date = date("Y-m-d H:i:00", strtotime( $request->date .' '. $request->time));
            $courseScheduleWhens->save();


            session()->flash('msg_success', '更新が完了しました');
            return redirect()->action('CourseScheduleController@index');
        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->back();    // 前の画面へ戻る
        }
    }

    /**
     * update	パラリンビクスコースの公開期間更新
     */
    public function paraUpdateOpenDay(Request $request, $id){
        try {
            $auth_id = Auth::user()->id;
            $intr_course = CourseSchedule::find($id);
            // 自分のスケジュールを更新しようとしているか確認
            if($intr_course->instructor_id <> $auth_id)throw new \Exception("不正な更新accessです");
            // 削除されたデータじゃないか確認
            if( $intr_course->delete_flag == 1 )throw new \Exception("このデータは更新できません");

            $intr_course->open_start_day=  date("Y-m-d H:i:00", strtotime($request->open_start_day));
            $intr_course->open_finish_day=  date("Y-m-d H:i:00", strtotime($request->open_finish_day));
            $intr_course->save();

            session()->flash('msg_success', '更新が完了しました');
            return redirect()->action('CourseScheduleController@index');
        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->back();    // 前の画面へ戻る
        }
    }

    /**
     * update	養成コースの更新
     */
    public function intrUpdate(Request $request, $id){
        try {
            // dd($request->date1);
            $intr_course = CourseSchedule::find($id);
            // 自分のスケジュールを更新しようとしているか確認
            if($intr_course->instructor_id <> $this->_auth_id)throw new \Exception("不正な更新accessです");
            // 受理済みか削除されたデータじゃないか確認
            if($intr_course->approval_flg > 5 || $intr_course->delete_flag == 1 )throw new \Exception("このデータは更新できません");

            $intr_course->price= $request->price ;
            $intr_course->erea= $request->erea ;
            $intr_course->venue= $request->venue ;
            $intr_course->notices= $request->notices ;
            $intr_course->comment= $request->comment ;
            $intr_course->approval_flg = 2 ;
            $intr_course->open_start_day=  date("Y-m-d H:i:00", strtotime($request->open_start_day));
            $intr_course->open_finish_day=  date("Y-m-d H:i:00", strtotime($request->open_finish_day));
            $intr_course->save();

            $this->updateCourseScheduleWhens( $id, $request->date1 ,1);
            $this->updateCourseScheduleWhens( $id, $request->date2 ,2);
            $this->updateCourseScheduleWhens( $id, $request->date3 ,3);
            $this->updateCourseScheduleWhens( $id, $request->date4 ,4);
            $this->updateCourseScheduleWhens( $id, $request->date5 ,5);
            $this->updateCourseScheduleWhens( $id, $request->date6 ,6);
            $this->updateCourseScheduleWhens( $id, $request->date7 ,7);
            $this->updateCourseScheduleWhens( $id, $request->date8 ,8);
            $this->updateCourseScheduleWhens( $id, $request->date9 ,9);
            $this->updateCourseScheduleWhens( $id, $request->date10 ,10);

            session()->flash('msg_success', '更新が完了しました');
            return redirect()->action('CourseScheduleController@index');
        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->back();    // 前の画面へ戻る
        }
    }

    /**
     * 養成コースに紐づくスケジュールを更新する
     */
    public function updateCourseScheduleWhens( $id, $requestDate, $howMany){
        if($requestDate){
            DB::table('course_schedule_whens')
                ->updateOrInsert(
                    ['course_schedules_id' => $id, 'howMany' => $howMany],
                    ['date' => $requestDate, 'instructor_id' => $this->_auth_id ]
                );
        }else{
            DB::table('course_schedule_whens')
                ->where('course_schedules_id' , $id)
                ->where('howMany', $howMany)
                ->delete();
        }
    }

    /**
     * update	養成コースの更新
     */
    public function intrUpdateOpenDay(Request $request, $id){
        try {
            $auth_id = Auth::user()->id;
            $intr_course = CourseSchedule::find($id);
            // 自分のスケジュールを更新しようとしているか確認
            if($intr_course->instructor_id <> $auth_id)throw new \Exception("不正な更新accessです");
            // 受理済みか削除されたデータじゃないか確認
            if($intr_course->delete_flag == 1 )throw new \Exception("このデータは更新できません");

            $intr_course->open_start_day=  date("Y-m-d H:i:00", strtotime($request->open_start_day));
            $intr_course->open_finish_day=  date("Y-m-d H:i:00", strtotime($request->open_finish_day));
            $intr_course->save();


            session()->flash('msg_success', '更新が完了しました');
            return redirect()->action('CourseScheduleController@index');
        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->back();    // 前の画面へ戻る
        }
    }

    /**
     * delete 申請済みのパラリンビクスコースを削除
     */
    public function paraDelete($id){
        try {
            $para_course = CourseSchedule::find($id);
            // ユーザーのコースか確認
            if( empty($para_course) || $para_course->instructor_id <> $this->_auth_id )throw new \Exception("不正なaccessです");

            // コースのIDを取得
            $para_course_id = $para_course->id;

            // 取得したコースを論理削除
            CourseSchedule::where('id', $para_course_id)
            ->update([
                'delete_flag' => 1
            ]);

            // 紐づいている日程を論理削除
            CourseScheduleWhens::where('course_schedules_id', $para_course_id)
            ->update([
                'delete_flag' => 1
            ]);
            session()->flash('msg_success', '削除が完了しました');
        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->back();    // 前の画面へ戻る
        }
        return redirect()->action('CourseScheduleController@index');
    }

    /**
     * delete 申請済みのイントラコースを削除
     */
    public function intrDelete($id){
        try {
            $auth_id = Auth::user()->id;
            $intr_course = CourseSchedule::find($id);
            // ユーザーのコースか確認
            if( empty($intr_course) || $intr_course->instructor_id <> $auth_id )throw new \Exception("不正なaccessです");
            if($intr_course->approval_flg > 2) throw new \Exception("削除しようとしたコースは受領済みの為削除できません。");

            // コースのIDを取得
            $intr_course_id = $intr_course->id;

            // 取得したコースを論理削除
            CourseSchedule::where('id', $intr_course_id)->update([
                                'delete_flag' => 1
            ]);

            // コースに紐づいているスケジュールを論理削除
            CourseScheduleWhens::where('course_schedules_id', $intr_course_id)
                            ->update([
                                'delete_flag' => 1
            ]);
            session()->flash('msg_success', '削除が完了しました');
        } catch (\Throwable $e) {
            session()->flash('msg_danger',$e->getMessage() );
            return redirect()->back();    // 前の画面へ戻る
        }
        return redirect()->action('CourseScheduleController@index');
    }


    public function getParaCourses(){
        $PCS = CourseSchedule::select('course_schedules.*','courses.course_name');
        // if($this->_auth_authority_id >= 7){
            $PCS = $PCS->where('instructor_id','=', $this->_auth_id );
        // }
        $PCS = $PCS->where('course_id', '<>', '6' );
        $PCS = $PCS->where('course_schedules.delete_flag', '0' );
        $PCS = $PCS->leftJoin('courses','courses.id','=','course_schedules.course_id');
        $PCS = $PCS->get();
        if($PCS){
            $PCS = $this->getApprovalNames($PCS);
        }

        return $PCS;

    }

}
