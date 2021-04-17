
<div class="LeftBOX">
  <a href="{{route('courseDetails.apply', ['id' => $customer->id ] )}}">
    <div class="button BOXin">新しいコースの申し込み</div>
  </a>
</div>


<table class="courseHistoryTable">
  <tr>
    <th>購入日</th>
    <th>購入コース</th>
    <th>金額</th>
    <th>入金確認</th>
  </tr>
  <?php foreach ($CoursePurchaseDetails as $key => $CoursePurchaseDetail) { ?>
    <tr>
      <td><?= date('Y年 m月 d日',  strtotime($CoursePurchaseDetail->date)) ?></td>
      <td><?= $CoursePurchaseDetail->course_name ?></td>
      <td><?= number_format($CoursePurchaseDetail->price) ?>円</td>
      <td><?= paymentConfirmation($CoursePurchaseDetail->pay_confirm , $CoursePurchaseDetail->payment_day ) ?></td>
    </tr>
  <?php  } ?>

</table>













<?php

  function paymentConfirmation($pay_confirm, $payment_day ){
    if($pay_confirm === 1){
      if($payment_day){
        $date = date('Y年 m月 d日',  strtotime($payment_day)) . " に確認済" ;
      }else{
        $date = "入金確認済み" ;
      }
    }else{
      $date = "未確認" ;
    }
    return $date;
  }


?>