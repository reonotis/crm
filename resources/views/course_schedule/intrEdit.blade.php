@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">インストラクター養成講座詳細</div>
                <div class="card-body">
                    <form action="{{route('courseSchedule.intrUpdate', ['id' => $intr_course->id ])}}" method="POST" class="form-inline my-2 my-lg-0">
                        @csrf
                        <table class="customerSearchTable">
                            <tr>
                                <th>実施コース</th>
                                <td><input type="text" class="formInput" name="course_title" value="{{ $intr_course->course_title }}"></td>
                            </tr>
                            <tr>
                                <th>料金</th>
                                <td>
                                    <div class="inputUnits">
                                    <input class="formInput inputUnit" type="number" name="price" value="{{ $intr_course->price }}" placeholder="360000" step="100" required >円
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>実施日時</th>
                                <td>
                                    <div class="inputDateTime">1回目<input type="datetime-local" class="formInput inputDatatimeLocal" name="date1" value="<?= $InstructorCourseSchedule[0]->date->format("Y-m-d")."T".$InstructorCourseSchedule[0]->date->format("H:i:s") ?>" required ></div>
                                    <div class="inputDateTime">2回目<input type="datetime-local" class="formInput inputDatatimeLocal" name="date2" value="<?= $InstructorCourseSchedule[1]->date->format("Y-m-d")."T".$InstructorCourseSchedule[1]->date->format("H:i:s") ?>" required ></div>
                                    <div class="inputDateTime">3回目<input type="datetime-local" class="formInput inputDatatimeLocal" name="date3" value="<?= $InstructorCourseSchedule[2]->date->format("Y-m-d")."T".$InstructorCourseSchedule[2]->date->format("H:i:s") ?>" required ></div>
                                    <div class="inputDateTime">4回目<input type="datetime-local" class="formInput inputDatatimeLocal" name="date4" value="<?= $InstructorCourseSchedule[3]->date->format("Y-m-d")."T".$InstructorCourseSchedule[3]->date->format("H:i:s") ?>" required ></div>
                                    <div class="inputDateTime">5回目<input type="datetime-local" class="formInput inputDatatimeLocal" name="date5" value="<?= $InstructorCourseSchedule[4]->date->format("Y-m-d")."T".$InstructorCourseSchedule[4]->date->format("H:i:s") ?>" required ></div>
                                    <div class="inputDateTime">6回目<input type="datetime-local" class="formInput inputDatatimeLocal" name="date6" value="<?php if(isset($InstructorCourseSchedule[5])) echo  $InstructorCourseSchedule[5]->date->format("Y-m-d")."T".$InstructorCourseSchedule[5]->date->format("H:i:s") ?>" ></div>
                                    <div class="inputDateTime">7回目<input type="datetime-local" class="formInput inputDatatimeLocal" name="date7" value="<?php if(isset($InstructorCourseSchedule[6])) echo  $InstructorCourseSchedule[6]->date->format("Y-m-d")."T".$InstructorCourseSchedule[6]->date->format("H:i:s") ?>" ></div>
                                    <div class="inputDateTime">8回目<input type="datetime-local" class="formInput inputDatatimeLocal" name="date8" value="<?php if(isset($InstructorCourseSchedule[7])) echo  $InstructorCourseSchedule[7]->date->format("Y-m-d")."T".$InstructorCourseSchedule[7]->date->format("H:i:s") ?>" ></div>
                                    <div class="inputDateTime">9回目<input type="datetime-local" class="formInput inputDatatimeLocal" name="date9" value="<?php if(isset($InstructorCourseSchedule[8])) echo  $InstructorCourseSchedule[8]->date->format("Y-m-d")."T".$InstructorCourseSchedule[8]->date->format("H:i:s") ?>" ></div>
                                    <div class="inputDateTime">10回目<input type="datetime-local" class="formInput inputDatatimeLocal" name="date10" value="<?php if(isset($InstructorCourseSchedule[9])) echo  $InstructorCourseSchedule[9]->date->format("Y-m-d")."T".$InstructorCourseSchedule[9]->date->format("H:i:s") ?>" ></div>
                                </td>
                            </tr>
                            <tr>
                                <th>エリア</th>
                                <td><input type="text" class="formInput" name="erea" value="{{ $intr_course->erea }}"></td>
                            </tr>
                            <tr>
                                <th>会場</th>
                                <td><input type="text" class="formInput" name="venue" value="{{ $intr_course->venue }}"></td>
                            </tr>
                            <tr>
                                <th>特記事項</th>
                                <td><input type="text" class="formInput" name="notices" value="{{ $intr_course->notices }}"></td>
                            </tr>
                            <tr>
                                <th>詳細</th>
                                <td><textarea name="comment" placeholder="詳細" class="formInput" >{{ $intr_course -> comment }}</textarea></td>
                            </tr>
                            <tr>
                                <th>公開期間</th>
                                <td>
                                    <div class="inputOpenDateTime">
                                        公開開始日<input type="datetime-local" name="open_start_day" class="formInput inputDatatimeLocal" value="{{ $intr_course->open_start_day->format('Y-m-d').'T'.$intr_course->open_start_day->format('H:i') }}" min="<?php echo date('Y-m-d',strtotime("-1 day"));?>T00:00" >から
                                    </div>
                                    <div class="inputOpenDateTime">
                                        公開終了日<input type="datetime-local" name="open_finish_day" class="formInput inputDatatimeLocal" value="{{ $intr_course->open_finish_day->format('Y-m-d').'T'.$intr_course->open_finish_day->format('H:i') }}" >まで
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <button class="btn btn-outline-success" onClick="return confilmUpdate()">申請内容を更新する</button>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    // dd($intr_schedule);
?>

<script>
function confilmUpdate(){
    var result = window.confirm('この内容で更新しますか？');
    if( result ) return true; return false;
}
</script>
<style>
    th{
        width:120px;
    }
</style>

@endsection


