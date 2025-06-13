<?php

use Fuel\Core\DB;

class Model_Task
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
    
    
    public function save()
    {
        if (isset($this->data['id'])) {
            return $this->update();
        } else {
            return $this->create();
        }
    }
    
    protected function create()
    {
        try {
            $this->data['created_at'] = date('Y-m-d H:i:s');
            $this->data['updated_at'] = date('Y-m-d H:i:s');
            
            $result = DB::insert('tasks')
                ->set($this->data)
                ->execute();
            
            $this->data['id'] = $result[0];
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    protected function update()
    {
        try {
            $this->data['updated_at'] = date('Y-m-d H:i:s');
            $id = $this->data['id'];
            unset($this->data['id']);
            
            DB::update('tasks')
                ->set($this->data)
                ->where('id', '=', $id)
                ->execute();
            
            $this->data['id'] = $id;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public static function find($type = 'first', $options = array())
    {
        if ($type === 'all') {
            $query = DB::select()->from('tasks');
            
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
            $tasks = array();
            
            foreach ($results as $result) {
                $task = new static();
                $task->data = $result;
                $tasks[] = $task;
            }
            
            return $tasks;
        }
        
        if (is_numeric($type)) {
            $result = DB::select()->from('tasks')->where('id', '=', $type)->execute()->as_array();
            
            if (!empty($result)) {
                $task = new static();
                $task->data = $result[0];
                return $task;
            }
        }
        
        return null;
    }
    
    public function delete()
    {
        if (isset($this->data['id'])) {
            return DB::delete('tasks')->where('id', '=', $this->data['id'])->execute();
        }
        return false;
    }
}