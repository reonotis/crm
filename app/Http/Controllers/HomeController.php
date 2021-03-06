<?php

namespace App\Http\Controllers;

use App\Models\InstructorCourse;
use App\Models\Claim;
use App\Models\CustomerCourseMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{

    private $_user;                 //Auth::user()
    private $_auth_id ;             //Auth::user()->id;
    private $_auth_authority_id ;   //権限

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
            return $next($request);
        });
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $adminMessage[] = "";
        $newApply = array();
        $unPayd = array();
        // 管理者だったら
        if($this->_auth_authority_id <= 5 ){
            // 未承認のコースを取得
            $UnAppCourse = InstructorCourse::where('approval_flg', 2 )->get();

            // 養成courseを終えたお客様を取得
            $compCourse = CustomerCourseMapping::select('customer_course_mapping.*', 'customers.name' )
            ->where('status', '>=',5)
            ->where('status', '<=',6)
            ->where('course_id', 6)
            ->join('customers', 'customers.id', 'customer_course_mapping.customer_id')
            ->join('instructor_courses', 'instructor_courses.id', 'customer_course_mapping.instructor_courses_id')
            ->get();
            // dd($compCourse);
            $adminMessage['UnAppCourse'] = $UnAppCourse;
            $adminMessage['compCourse'] = $compCourse;

            // 未入金のお客様を取得
            $newApply = CustomerCourseMapping::where('status', 0 )->get();
            $unPayd = Claim::where('status', 1 )->get();
        }
        $NgAppCourse = InstructorCourse::where('approval_flg', 1 )->where('instructor_id', $this->_auth_id )->get();
        $intrMessage['NgAppCourse'] = $NgAppCourse;

        return view('home', compact('adminMessage','intrMessage','newApply' , 'unPayd'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index2()
    {
        return view('home2');
    }
}
