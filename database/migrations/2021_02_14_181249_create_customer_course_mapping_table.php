<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerCourseMappingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_course_mapping', function (Blueprint $table) {
            $table->bigIncrements('id')                  ->comment('ID');
            $table->integer('customer_id')               ->comment('顧客ID');
            $table->date('date')                         ->comment('購入日');
            $table->integer('instructor_id')             ->comment('インストラクターID');
            $table->integer('instructor_courses_id')     ->comment('購入したコースID');
            $table->integer('price')                     ->comment('料金');
            $table->date('limit_day')      ->nullable()  ->comment('入金期日');
            $table->integer('pay_confirm') ->default('0')->comment('入金確認 0:未入金 1:cancel 2:入金済');
            $table->date('payment_day')    ->nullable()  ->comment('入金日');
            $table->integer('status')      ->default('0')->comment('状態 0:申込中 1:入金依頼mail送信済 2:cancel 3:入確済 5:受講完 6:登録依頼mail送信済 7:契約完了 8:入金依頼mail送信済み 9:初期費用入金 10:イントラ登録済');
            $table->integer('claim_id')    ->nullable()  ->comment('請求ID');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時')	;
            $table->integer('created_by')  ->nullable()  ->comment('作成者')	;
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
            $table->integer('updated_by')  ->nullable()  ->comment('更新者')	;
            $table->boolean('delete_flag') ->default('0')->comment('削除フラグ');
            // $ php artisan make:seeder CoursePurchaseDetailsTableSeeder
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_course_mapping');
    }
}
