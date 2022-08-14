<?php

namespace App\Services;

use App\Traits\Singleton;
use Illuminate\Support\Facades\Redis;

class RedisListService
{
    use Singleton;

    public const QUEUED = 'url_processing';
    public const DOWNLOADING = 'url_downloading';
    public const PROCESSING = 'url_processing';
    public const FINALIZING = 'url_finalizing';



    private $redis;
    private $prefix = 'dti_';

    protected function init()
    {
        $this->redis = Redis::connection('default');
    }

    public function clearFromAllLists($urlId){
        foreach([
            self::QUEUED, self::DOWNLOADING, self::PROCESSING, self::FINALIZING
        ] as $key){
            $this->removeFromList($key, $urlId);
        }
    }

    private function prependList($list)
    {
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
