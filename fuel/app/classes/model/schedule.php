<?php

use Fuel\Core\DB;

class Model_Schedule
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
            
            $result = DB::insert('schedules')
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
            
            DB::update('schedules')
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
            $query = DB::select()->from('schedules');
            
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
            $schedules = array();
            
            foreach ($results as $result) {
                $schedule = new static();
                $schedule->data = $result;
                $schedules[] = $schedule;
            }
            
            return $schedules;
        }
        
        if (is_numeric($type)) {
            $result = DB::select()->from('schedules')->where('id', '=', $type)->execute()->as_array();
            
            if (!empty($result)) {
                $schedule = new static();
                $schedule->data = $result[0];
                return $schedule;
            }
        }
        
        return null;
    }
    
    // 削除
    public function delete()
    {
        if (isset($this->data['id'])) {
            return DB::delete('schedules')->where('id', '=', $this->data['id'])->execute();
        }
        return false;
    }
}