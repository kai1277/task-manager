<?php

use Fuel\Core\Session;

class Controller_User extends Controller
{
    public function action_register()
    {
        return View::forge('user/register');
    }

    public function action_create()
    {
        $val = Validation::forge();
        $val->add_field('username', 'ユーザー名', 'required|max_length[50]');
        $val->add_field('email', 'メール', 'required|valid_email');
        $val->add_field('password', 'パスワード', 'required|min_length[6]');

        if ($val->run()) {
            $user = Model_User::forge(array(
                'name' => Input::post('username'),
                'email' => Input::post('email'),
                'password' => password_hash(Input::post('password'), PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));
        
            if ($user->save()) {
                Session::set('user_id', $user->id);
                Session::set('name', $user->name);
    
                return Response::redirect('task');
            }
        } else {
            // エラー表示
            return View::forge('user/register', array('errors' => $val->error()));
        }
    }

    // ログイン処理
    public function action_login()
    {
        if (Input::method() == 'POST') {
            $email = Input::post('email');
            $password = Input::post('password');

            // メールアドレスでユーザーを検索
            $user = Model_User::find('first', array(
                'where' => array(array('email', $email))
            ));

            if ($user && password_verify($password, $user->password)) {
                // ログイン成功：セッションにユーザーIDを保存
                Session::set('user_id', $user->id);
                Session::set('name', $user->name);
    
                // タスク一覧にリダイレクト
                return Response::redirect('task');
            }
        }

        return View::forge('user/login');
    } 
    

    // ログアウト処理
    public function action_logout()
    {
        Session::delete('user_id');
        Session::delete('name');
        return Response::redirect('user/login');
    }
}