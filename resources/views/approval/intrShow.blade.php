@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="fullWidth">
            <button class="btn btn-light btn-sm" type="button" onClick="history.back()">戻る</button>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">インストラクター養成講座 承認内容確認</div>
                <div class="card-body">
                    <table class="customerSearchTable">
                        <tr>
                            <th>講師</th>
                            <td>{{$courseSchedule->name }}</td>
                        </tr>
                        <tr>
                            <th>講座</th>
                            <td>{{$courseScheduleList->course_title}}</td>
                        </tr>
                        <tr>
                            <th>料金</th>
                            <td>{{number_format($courseSchedule->price)}}円</td>
                        </tr>
                        <tr>
                            <th>開催日程</th>
                            <td>
                                1回目{{ $courseScheduleList->date1->format('Y/m/d') }}～<br>
                                2回目{{ $courseScheduleList->date2->format('Y/m/d') }}～<br>
                                3回目{{ $courseScheduleList->date3->format('Y/m/d') }}～<br>
                                4回目{{ $courseScheduleList->date4->format('Y/m/d') }}～<br>
                                5回目{{ $courseScheduleList->date5->format('Y/m/d') }}～<br>
                                @if($courseScheduleList->date6)
                                    6回目　{{$courseScheduleList->date6->format('Y/m/d')}}<br>
                                @endif
                                @if($courseScheduleList->date7)
                                    7回目　{{$courseScheduleList->date7->format('Y/m/d')}}<br>
                                @endif
                                @if($courseScheduleList->date8)
                                    8回目　{{$courseScheduleList->date8->format('Y/m/d')}}<br>
                                @endif
                                @if($courseScheduleList->date9)
                                    9回目　{{$courseScheduleList->date9->format('Y/m/d')}}<br>
                                @endif
                                @if($courseScheduleList->date10)
                                    10回目　{{$courseScheduleList->date10->format('Y/m/d')}}<br>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>エリア</th>
                            <td>{{$courseSchedule->erea}}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>会場</th>
                            <td>{{$courseSchedule->venue}}</td>
                        </tr>
                        <tr>
                            <th>特記事項</th>
                            <td>{{$courseSchedule->notices}}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>詳細</th>
                            <td>{!! nl2br(e($courseSchedule -> comment)) !!}</td>
                        </tr>
                        <tr>
                            <th>公開期間</th>
                            <td>{{$para_course->open_start_day->format('Y/m/d H:i') }}　～　{{$para_course->open_finish_day->format('Y/m/d H:i') }}</td>
                        </tr>
                        @if( count($ApprovalComments) >= 1)
                            <tr>
                                <th>コメント</th>
                                <td>
                                    @foreach($ApprovalComments as $ApprovalComment)
                                        {{$ApprovalComment->comment}}<br>
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <th>状態</th>
                            <td>{{$courseSchedule->approval_name}}</td>
                        </tr>
                        @if($courseSchedule->approval_flg == 2 )
                            <tr>
                                <th>承認 / 取下</th>
                                <td>
                                    <form action="{{route('approval.update',['id' => $courseSchedule->id ])}}" method="POST">
                                        @csrf
                                        <input type="text" name="appComment" class="formInput" placeholder="コメントを入力" >
                                        <button class="btn btn-outline-danger"  name="NG" value="NG" onClick="return confilmDelete()">取り下げる</button>
                                        <button class="btn btn-outline-success" name="OK" value="OK"  >承認する</button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    // dd($ApprovalComments);
?>

<script>
function confilmDelete(){
    var result = window.confirm('この申請を取り下げますか？');
    if( result ) {
        return true;
    } else {
        return false;
    }
}
</script>
<style>
    th{
        width:120px;
    }
</style>

@endsection

