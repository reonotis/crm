<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => '藤澤怜臣',
                'read' => 'フジサワ レオン',
                'email' => 'test@test.jp',
                'password' => Hash::make('reonotis'),
                'authority_id' => '1',
                'enrolled_id' => '1',
            ],[
                'name' => '穐里 明美',
                'read' => 'アキサト アケミ',
                'email' => 'test1@test.jp',
                'password' => Hash::make('akemi'),
                'authority_id' => '2',
                'enrolled_id' => '1',
            ],[
                'name' => '西川 薫',
                'read' => 'ニシカワ カオリ',
                'email' => 'test2@test.jp',
                'password' => Hash::make('kaori'),
                'authority_id' => '3',
                'enrolled_id' => '1',
            ],[
                'name' => 'インストラクターAさん',
                'read' => 'インストラクターエー',
                'email' => 'test3@test.jp',
                'password' => Hash::make('reonotis'),
                'authority_id' => '7',
                'enrolled_id' => '5',
            ],[
                'name' => 'インストラクターBさん',
                'read' => 'インストラクタービー',
                'email' => 'test4@test.jp',
                'password' => Hash::make('reonotis'),
                'authority_id' => '9',
                'enrolled_id' => '9',
            ]
        ]);
    }
}
