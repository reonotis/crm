@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif


                    <!-- 管理者へのメッセージ -->
                    @if(isset($adminMessage['UnAppCourse']))
                        <a href="<?= url('').'/approval/index'  ?>" class="messegeLink" >申請中のコースが <?= count($adminMessage['UnAppCourse']) ?> 件あります<br></a>
                    @endif
                    @if(isset($adminMessage['compCourse']))
                        @foreach($adminMessage['compCourse'] as $data)
                            <a href="" class="messegeLink" ><?= $data->name ?>様が養成courseを終了しました<br></a>
                        @endforeach
                    @endif

                    <!-- インストラクターへのメッセージ -->
                    @if(count($intrMessage['NgAppCourse']))
                        <a href="<?= url('').'/courseSchedule/index'  ?>" class="messegeLink" >差し戻されたコースが <?= count($intrMessage['NgAppCourse']) ?> 件あります<br></a>
                    @endif


                </div>
            </div>
        </div>
    </div>
</div>
@endsection


<style>
.messegeLink{
    display: block;

    margin-top :10px;
}
</style>