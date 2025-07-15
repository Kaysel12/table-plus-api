<?php

namespace App\Utils;

use Illuminate\Support\Facades\Redis;

class CacheHelper
{
    public static function clearTaskCacheForUser(int $userId): void
    {
        $pattern = "tasks_paginated_user_{$userId}_*";
        $keys = Redis::keys($pattern);

        if (!empty($keys)) {
            Redis::del(...$keys);
        }
    }
}