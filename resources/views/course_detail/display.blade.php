@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="fullWidth">
            <button class="btn btn-outline-dark btn-sm" type="button" onClick="history.back()">戻る</button>
        </div>

        <h3>開催コース詳細</h3>

        <div class="coursesErea" >
            <h4>コース概要</h4>
            <div class="courseDetailRow">
                <div class="courseDetailTitle">コース名</div>
                <div class="courseDetailContent"><?= $IC->course_name ."　". $IC->course_title  ?></div>
            </div>

            <div class="courseDetailRow">
                <div class="courseDetailTitle">インストラクター</div>
                <div class="courseDetailContent"><?= $IC->name ?></div>
            </div>

            <div class="courseDetailRow">
                <div class="courseDetailTitle">実施日</div>
                <div class="courseDetailContent">
                    @foreach($ICS as $date)
                        <div>{{$date->date->format('Y年m月d日 H:i～') }}</div>
                    @endforeach
                </div>
            </div>

            <div class="courseDetailRow">
                <div class="courseDetailTitle">エリア</div>
                <div class="courseDetailContent"><?= $IC->erea ?></div>
            </div>
            <div class="courseDetailRow">
                <div class="courseDetailTitle">会場</div>
                <div class="courseDetailContent"><?= $IC->venue ?></div>
            </div>

<br>
<br>
            <h4>参加者のスケジュール</h4>
            <table class="scheduleListTable" >
                <tr>
                    <th>日時</th>
                    <th>内容</th>
                    <th>参加者</th>
                    <th>受講状態</th>
                    <th>更新</th>
                </tr>
                @foreach($customer_schedules as $data)
                    <tr>
                        <td>{{ $data->date_time->format('Y-m-d　H:i～') }}</td>
                        <td>{{ $data->howMany }}回目</td>
                        <td>{{ $data->name }}</td>
                        <td>
                            <?php if($data->status){
                                echo "受講済み";
                            }else{
                                ?>
                                <a href="{{ route('course_detail.completCustomerSchedule', ['id' => $data->id ]) }}" onclick="return confirmFunction1()">受講済みにする</a>
                                <?php
                            }
                            ?>
                        </td>
                        <td>
                            <?php if(!$data->status){ ?>
                                <a href="{{ route('course_detail.edit', ['id' => $data->id ]) }}" >日時を修正</a>
                            <?php } ?>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>


<?php
    // dd($IC, $ICS, $customer_schedules);
?>

@endsection

<script>
    function confirmFunction1() {
        //ret変数に確認ダイアログの結果を代入する。
        result = window.confirm('このスケジュールを受講済みにします。\n宜しいですか？\nこの操作は元には戻せません');
        if( result ) return true; return false;

    }
</script>