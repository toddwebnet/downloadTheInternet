<?php

namespace App\Services;

use App\Traits\Singleton;
use Illuminate\Support\Facades\Redis;

class RedisListService
{
    use Singleton;

    private $redis;
    private $prefix = 'dti_';

    protected function init()
    {
        $this->redis = Redis::connection('default');
    }

    private function prependList($list)
    {
        return $list;
        return $this->prefix . $list;
    }

    private function redisSet($list, $data)
    {
        $key = $this->prependList($list);
        $this->redis->set($key, $data);
    }

    private function redisGet($list)
    {
        $key = $this->prependList($list);
        return $this->redis->get($key);
    }

    public function clearList($list)
    {
        $key = $this->prependList($list);
        $this->redis->del($key);
    }

    public function getList($list)
    {
        $data = $this->redisGet($list);
        if ($data === null) {
            return [];
        } else {
            return json_decode($data);
        }
    }

    public function isInList($list, $key)
    {
        return in_array($key, $this->getList($list));
    }

    public function addToList($list, $key)
    {
        $data = $this->getList($list);
        if (!in_array($key, $data)) {
            $data[] = $key;
            $this->redisSet($list, json_encode($data));
        }
    }

    public function removeFromList($list, $key)
    {
        $data = $this->getList($list);
        if (in_array($key, $data)) {
            unset($data[array_search($key, $data)]);
            $this->redisSet($list, json_encode(array_values($data)));
        }
    }
}
