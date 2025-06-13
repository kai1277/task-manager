<?php

use Fuel\Core\DB;

class Model_Class
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
            return $this->update();
        } else {
            return $this->create();
        }
    }
    
    // 新規作成
    protected function create()
    {
        try {
            $this->data['created_at'] = date('Y-m-d H:i:s');
            $this->data['updated_at'] = date('Y-m-d H:i:s');
            
            $result = DB::insert('classes')
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
            
            DB::update('classes')
                ->set($this->data)
                ->where('id', '=', $id)
                ->execute();
            
            $this->data['id'] = $id;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // 検索
    public static function find($type = 'first', $options = array())
    {
        if ($type === 'all') {
            $query = DB::select()->from('classes');
            
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
            $classes = array();
            
            foreach ($results as $result) {
                $class = new static();
                $class->data = $result;
                $classes[] = $class;
            }
            
            return $classes;
        }
        
        if (is_numeric($type)) {
            $result = DB::select()->from('classes')->where('id', '=', $type)->execute()->as_array();
            
            if (!empty($result)) {
                $class = new static();
                $class->data = $result[0];
                return $class;
            }
        }
        
        return null;
    }
    
    // 削除
    public function delete()
    {
        if (isset($this->data['id'])) {
            return DB::delete('classes')->where('id', '=', $this->data['id'])->execute();
        }
        return false;
    }
}