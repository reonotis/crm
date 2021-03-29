@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="fullWidth">
            <button class="btn btn-light btn-sm" type="button" onClick="history.back()">戻る</button>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">パラリンビクス講座 承認内容確認</div>
                <div class="card-body">
                    <table class="customerSearchTable">
                        <tr>
                            <th>講師</th>
                            <td>{{$courseSchedules->f_name ." ". $courseSchedules->l_name}}</td>
                        </tr>
                        <tr>
                            <th>講座</th>
                            <td>{{$courseSchedules->name}}</td>
                        </tr>
                        <tr>
                            <th>料金</th>
                            <td>{{number_format($courseSchedules->price)}}円</td>
                        </tr>
                        <tr>
                            <th>実施日時</th>
                            <td>{{ $courseSchedules->date->format('Y/m/d'). "　" . date('H:i', strtotime($courseSchedules->open_time)) }}～</td>
                        </tr>
                        <tr>
                            <th>エリア</th>
                            <td>{{$courseSchedules->erea}}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>会場</th>
                            <td>{{$courseSchedules->venue}}</td>
                        </tr>
                        <tr>
                            <th>特記事項</th>
                            <td>{{$courseSchedules->notices}}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>詳細</th>
                            <td>{!! nl2br(e($courseSchedules -> comment)) !!}</td>
                        </tr>
                        <tr>
                            <th>状態</th>
                            <td>{{$courseSchedules->approval_name}}</td>
                        </tr>
                        @if($courseSchedules->approval_flg == 2 )
                            <tr>
                                <th>承認 / 取下</th>
                                <td>
                                    <form action="{{route('approval.update',['id' => $courseSchedules->id ])}}" method="POST">
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
// dd($courseSchedules);
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


