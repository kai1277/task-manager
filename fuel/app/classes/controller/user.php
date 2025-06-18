<?php

use Fuel\Core\Session;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\View;
use Fuel\Core\Validation;
use Fuel\Core\Log;

class Controller_User extends Controller_Base
{
    // ユーザー関連のページはログイン不要
    protected function requires_login()
    {
        return false;
    }

    public function action_register()
    {
        return View::forge('user/register');
    }

    public function action_create()
    {
        $val = Validation::forge();
        
        // 改善されたバリデーションルール
        $val->add_field('username', 'ユーザー名', 'required|max_length[50]|min_length[2]');
        $val->add_field('email', 'メールアドレス', 'required|valid_email|max_length[255]');
        $val->add_field('password', 'パスワード', 'required|min_length[6]|max_length[255]');
        $val->add_field('password_confirm', 'パスワード（確認）', 'required|match_field[password]');

        // メールアドレスの重複チェックを追加
        $existing_user = Model_User::find('first', array(
            'where' => array(array('email', Input::post('email')))
        ));
        
        if ($existing_user) {
            // 手動でエラーを追加
            $val->add_error('email', 'このメールアドレスは既に登録されています');
        }

        if ($val->run()) {
            try {
                $user = Model_User::forge(array(
                    'name' => Input::post('username'),
                    'email' => Input::post('email'),
                    'password' => password_hash(Input::post('password'), PASSWORD_DEFAULT),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ));
                
                if ($user->save()) {
                    // 登録成功後、自動ログイン
                    Session::set('user_id', $user->id);
                    Session::set('name', $user->name);
                    
                    // 成功メッセージをセッションに保存
                    Session::set_flash('success', 'アカウントが正常に作成されました。ようこそ！');
                    
                    return Response::redirect('task');
                } else {
                    throw new Exception('ユーザーの保存に失敗しました');
                }
            } catch (Exception $e) {
                // エラーログに記録
                Log::error('User registration failed: ' . $e->getMessage());
                
                // エラーメッセージを表示
                return View::forge('user/register', array(
                    'errors' => array(
                        'database' => (object)array(
                            'get_message' => function() { 
                                return 'データベースエラーが発生しました。もう一度お試しください。'; 
                            }
                        )
                    )
                ));
            }
        } else {
            // バリデーションエラーの場合、入力値を保持してビューに渡す
            return View::forge('user/register', array(
                'errors' => $val->error(),
                'old_input' => array(
                    'username' => Input::post('username'),
                    'email' => Input::post('email')
                )
            ));
        }
    }

    // ログイン処理（改善版）
    public function action_login()
    {
        if (Input::method() == 'POST') {
            $email = trim(Input::post('email'));
            $password = Input::post('password');

            // 入力値のバリデーション
            if (empty($email) || empty($password)) {
                return View::forge('user/login', array(
                    'error' => 'メールアドレスとパスワードを入力してください',
                    'old_email' => $email
                ));
            }

            // メールアドレスでユーザーを検索
            $user = Model_User::find('first', array(
                'where' => array(array('email', $email))
            ));

            if ($user && password_verify($password, $user->password)) {
                // ログイン成功：セッションにユーザーIDを保存
                Session::set('user_id', $user->id);
                Session::set('name', $user->name);
                
                // セッション再生成（セキュリティ強化）
                Session::instance()->rotate();
                
                // 成功メッセージ
                Session::set_flash('success', 'ログインしました。おかえりなさい！');
                
                return Response::redirect('task');
            } else {
                // ログイン失敗（セキュリティのため詳細は伏せる）
                return View::forge('user/login', array(
                    'error' => 'メールアドレスまたはパスワードが間違っています',
                    'old_email' => $email
                ));
            }
        }

        return View::forge('user/login');
    }

    // ログアウト処理（改善版）
    public function action_logout()
    {
        // セッションデータをクリア
        Session::delete('user_id');
        Session::delete('name');
        
        // セッションを完全に破棄
        Session::destroy();
        
        // 成功メッセージ
        Session::set_flash('success', 'ログアウトしました。');
        
        return Response::redirect('user/login');
    }
    
    // パスワードリセット用（将来の機能拡張）
    public function action_forgot_password()
    {
        // 将来実装予定
        return View::forge('user/forgot_password');
    }
}