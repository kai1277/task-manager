<?php

use Fuel\Core\DB;

class Model_User
{
    public static function forge($data = array())
    {
        return new static($data);
    }
    
    protected $data = array();
    
    public function __construct($data = array())
    {
        $this->data = $data;
    }

    public function __toString()
    {
        return isset($this->data['name']) ? $this->data['name'] : '';
    }
    
    // プロパティアクセス用
    public function __get($property)
    {
        return isset($this->data[$property]) ? $this->data[$property] : null;
    }
    
    public function __set($property, $value)
    {
        $this->data[$property] = $value;
    }
    
    // 保存処理
    public function save()
    {
        if (isset($this->data['id'])) {
            // 更新処理
            return $this->update();
        } else {
            // 新規作成処理
            return $this->create();
        }
    }
    
    // 新規作成
    protected function create()
    {
        try {
            $this->data['created_at'] = date('Y-m-d H:i:s');
            $this->data['updated_at'] = date('Y-m-d H:i:s');
            
            $result = DB::insert('users')
                ->set($this->data)
                ->execute();
            
            $this->data['id'] = $result[0];
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // 更新
    protected function update()
    {
        try {
            $this->data['updated_at'] = date('Y-m-d H:i:s');
            $id = $this->data['id'];
            unset($this->data['id']);
            
            DB::update('users')
                ->set($this->data)
                ->where('id', '=', $id)
                ->execute();
            
            $this->data['id'] = $id;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // 1件検索
    public static function find($type = 'first', $options = array())
    {
        if ($type === 'first') {
            $query = DB::select()->from('users');
            
            if (isset($options['where'])) {
                foreach ($options['where'] as $condition) {
                    $query->where($condition[0], '=', $condition[1]);
                }
            }
            
            $result = $query->execute()->as_array();
            
            if (!empty($result)) {
                $user = new static();
                $user->data = $result[0];
                return $user;
            }
        }
        
        if ($type === 'all') {
            $query = DB::select()->from('users');
            
            if (isset($options['where'])) {
                foreach ($options['where'] as $condition) {
                    $query->where($condition[0], '=', $condition[1]);
                }
            }
            
            if (isset($options['order_by'])) {
                foreach ($options['order_by'] as $column => $direction) {
                    $query->order_by($column, $direction);
                }
            }
            
            $results = $query->execute()->as_array();
            $users = array();
            
            foreach ($results as $result) {
                $user = new static();
                $user->data = $result;
                $users[] = $user;
            }
            
            return $users;
        }
        
        return null;
    }
    
    // ID指定検索
    public static function find_by_id($id)
    {
        $result = DB::select()->from('users')->where('id', '=', $id)->execute()->as_array();
        
        if (!empty($result)) {
            $user = new static();
            $user->data = $result[0];
            return $user;
        }
        
        return null;
    }
    
    // 削除
    public function delete()
    {
        if (isset($this->data['id'])) {
            return DB::delete('users')->where('id', '=', $this->data['id'])->execute();
        }
        return false;
    }
}