<?php

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
                'username' => Input::post('username'),
                'email' => Input::post('email'),
                'password' => password_hash(Input::post('password'), PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));
            $user->save();
            return Response::redirect('/');
        } else {
            // エラー表示
            return View::forge('user/register', array('errors' => $val->error()));
        }

        Session::set('user_id', $user->id); // ユーザーIDをセッションに保存
    }
}