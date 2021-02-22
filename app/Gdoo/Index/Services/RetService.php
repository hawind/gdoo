<?php namespace Gdoo\Index\Services;

class RetService
{
    public $data = null;

    public static function make($data = null)
    {
        $me = new static();
        $me->data = collect();
        $me->set($data);
        return $me;
    }

    public function set($key, $value = null)
    {
        if (empty($key)) {
            return;
        }
        if (empty($value)) {
            $this->data = $this->data->merge($key);
        } else {
            $this->data[$key] = $value;
        }
    }
    public function error($msg)
    {
        $this->data['msg'] = $msg;
        $this->data['success'] = false;
        return json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }

    public function success($msg)
    {
        $this->data['msg'] = $msg;
        $this->data['success'] = true;
        return json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }
}
