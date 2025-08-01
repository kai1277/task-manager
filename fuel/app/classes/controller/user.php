<?php

use Fuel\Core\Session;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\View;
use Fuel\Core\Validation;
use Fuel\Core\Log;
use Fuel\Core\Security;

class Controller_User extends Controller_Base
{
    // ユーザー関連のページはログイン不要（マイページ以外）
    protected function requires_login()
    {
        // マイページ関連のアクションのみログイン必須
        $action = $this->request->action;
        $login_required_actions = array('mypage', 'update_profile', 'change_password');
        
        return in_array($action, $login_required_actions);
    }

    public function action_register()
    {
        return View::forge('user/register', array(
            'csrf_token' => Security::fetch_token()
        ));
    }

    public function action_create()
    {
        try {
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
                $user = Model_User::forge(array(
                    'name' => Security::clean(Input::post('username')),
                    'email' => Security::clean(Input::post('email')),
                    'password' => password_hash(Input::post('password'), PASSWORD_DEFAULT),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ));
                
                if ($user->save()) {
                    // 登録成功後、自動ログイン
                    Session::set('user_id', $user->id);
                    Session::set('name', $user->name);
                    Session::set('user_email', $user->email); // メールアドレスも保存
                    
                    // 成功メッセージをセッションに保存
                    Session::set_flash('success', 'アカウントが正常に作成されました。ようこそ！');
                    
                    return Response::redirect('task');
                } else {
                    throw new Exception('ユーザーの保存に失敗しました');
                }
            } else {
                // バリデーションエラーの場合、入力値を保持してビューに渡す
                return View::forge('user/register', array(
                    'errors' => $val->error(),
                    'old_input' => array(
                        'username' => Security::clean(Input::post('username')),
                        'email' => Security::clean(Input::post('email'))
                    ),
                    'csrf_token' => Security::fetch_token()
                ));
            }
            
        } catch (Exception $e) {
            return $this->handle_error($e, 'ユーザー登録でエラーが発生しました');
        }
    }

    // ログイン処理（改善版）
    public function action_login()
    {
        if (Input::method() == 'POST') {
            try {
                $email = trim(Input::post('email'));
                $password = Input::post('password');

                // 入力値のバリデーション
                if (empty($email) || empty($password)) {
                    return View::forge('user/login', array(
                        'error' => 'メールアドレスとパスワードを入力してください',
                        'old_email' => Security::clean($email),
                        'csrf_token' => Security::fetch_token()
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
                    Session::set('user_email', $user->email); // メールアドレスも保存
                    
                    // セッション再生成（セキュリティ強化）
                    Session::instance()->rotate();
                    
                    // 成功メッセージ
                    Session::set_flash('success', 'ログインしました。おかえりなさい！');
                    
                    return Response::redirect('task');
                } else {
                    // ログイン失敗（セキュリティのため詳細は伏せる）
                    return View::forge('user/login', array(
                        'error' => 'メールアドレスまたはパスワードが間違っています',
                        'old_email' => Security::clean($email),
                        'csrf_token' => Security::fetch_token()
                    ));
                }
                
            } catch (Exception $e) {
                return $this->handle_error($e, 'ログイン処理でエラーが発生しました');
            }
        }

        return View::forge('user/login', array(
            'csrf_token' => Security::fetch_token()
        ));
    }

    // ログアウト処理（改善版）
    public function action_logout()
    {
        try {
            // セッションデータをクリア
            Session::delete('user_id');
            Session::delete('name');
            Session::delete('user_email');
            
            // セッションを完全に破棄
            Session::destroy();
            
            // 成功メッセージ
            Session::set_flash('success', 'ログアウトしました。');
            
            return Response::redirect('user/login');
            
        } catch (Exception $e) {
            return $this->handle_error($e, 'ログアウト処理でエラーが発生しました');
        }
    }

    // マイページ表示（新規追加）
    public function action_mypage()
    {
        try {
            // ユーザー情報を取得してセッションに保存（最新情報を取得）
            $user = Model_User::find('first', array(
                'where' => array(array('id', Session::get('user_id')))
            ));
            
            if ($user) {
                Session::set('user_email', $user->email);
                Session::set('name', $user->name);
            }
            
            return View::forge('user/mypage', array(
                'csrf_token' => Security::fetch_token()
            ));
            
        } catch (Exception $e) {
            return $this->handle_error($e, 'マイページの表示でエラーが発生しました');
        }
    }
    
    // プロフィール更新（新規追加）
    public function action_update_profile()
    {
        if (Input::method() != 'POST') {
            return Response::redirect('user/mypage');
        }
        
        try {
            $val = Validation::forge();
            $val->add_field('username', 'ユーザー名', 'required|max_length[50]|min_length[2]');
            $val->add_field('email', 'メールアドレス', 'required|valid_email|max_length[255]');
            
            // 他のユーザーが使用していないかチェック
            $existing_user = Model_User::find('first', array(
                'where' => array(
                    array('email', Input::post('email')),
                    array('id', '!=', Session::get('user_id'))
                )
            ));
            
            if ($existing_user) {
                Session::set_flash('error', 'このメールアドレスは既に他のユーザーによって使用されています。');
                return Response::redirect('user/mypage');
            }
            
            if ($val->run()) {
                $user = Model_User::find('first', array(
                    'where' => array(array('id', Session::get('user_id')))
                ));
                
                if ($user) {
                    $user->name = Security::clean(Input::post('username'));
                    $user->email = Security::clean(Input::post('email'));
                    $user->updated_at = date('Y-m-d H:i:s');
                    
                    if ($user->save()) {
                        // セッション情報を更新
                        Session::set('name', $user->name);
                        Session::set('user_email', $user->email);
                        
                        Session::set_flash('success', 'プロフィールを更新しました。');
                    } else {
                        throw new Exception('プロフィールの更新に失敗しました');
                    }
                }
            } else {
                $errors = array();
                foreach ($val->error() as $field => $error) {
                    $errors[] = $error->get_message();
                }
                Session::set_flash('error', implode('<br>', $errors));
            }
            
        } catch (Exception $e) {
            return $this->handle_error($e, 'プロフィール更新でエラーが発生しました');
        }
        
        return Response::redirect('user/mypage');
    }
    
    // パスワード変更（新規追加）
    public function action_change_password()
    {
        if (Input::method() != 'POST') {
            return Response::redirect('user/mypage');
        }
        
        try {
            $current_password = Input::post('current_password');
            $new_password = Input::post('new_password');
            $confirm_password = Input::post('confirm_password');
            
            // バリデーション
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                Session::set_flash('error', 'すべての項目を入力してください。');
                return Response::redirect('user/mypage');
            }
            
            if (strlen($new_password) < 6) {
                Session::set_flash('error', '新しいパスワードは6文字以上で入力してください。');
                return Response::redirect('user/mypage');
            }
            
            if ($new_password !== $confirm_password) {
                Session::set_flash('error', '新しいパスワードが一致しません。');
                return Response::redirect('user/mypage');
            }
            
            if ($current_password === $new_password) {
                Session::set_flash('error', '新しいパスワードは現在のパスワードと異なるものを設定してください。');
                return Response::redirect('user/mypage');
            }
            
            // 現在のパスワードを確認
            $user = Model_User::find('first', array(
                'where' => array(array('id', Session::get('user_id')))
            ));
            
            if (!$user || !password_verify($current_password, $user->password)) {
                Session::set_flash('error', '現在のパスワードが間違っています。');
                return Response::redirect('user/mypage');
            }
            
            // パスワードを更新
            $user->password = password_hash($new_password, PASSWORD_DEFAULT);
            $user->updated_at = date('Y-m-d H:i:s');
            
            if ($user->save()) {
                // セキュリティのためセッションを再生成
                Session::instance()->rotate();
                
                Session::set_flash('success', 'パスワードを変更しました。');
            } else {
                throw new Exception('パスワードの変更に失敗しました');
            }
            
        } catch (Exception $e) {
            return $this->handle_error($e, 'パスワード変更でエラーが発生しました');
        }
        
        return Response::redirect('user/mypage');
    }
    
    // パスワードリセット用（将来の機能拡張）
    public function action_forgot_password()
    {
        // 将来実装予定
        return View::forge('user/forgot_password', array(
            'csrf_token' => Security::fetch_token()
        ));
    }
}