<?php

/*
 * This file is part of the nilsir/laravel-esign.
 *
 * (c) nilsir <nilsir@qq.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Ice\Tool\Support;


use Ice\Tool\Singleton;

/**
 * @method static void alert(string $message, array $context = [])
 * @method static void critical(string $message, array $context = [])
 * @method static void debug(string $message, array $context = [])
 * @method static void emergency(string $message, array $context = [])
 * @method static void error(string $message, array $context = [])
 * @method static void info(string $message, array $context = [])
 * @method static void log($level, string $message, array $context = [])
 * @method static void notice(string $message, array $context = [])
 * @method static void warning(string $message, array $context = [])
 * @method static void write(string $level, string $message, array $context = [])
 * @method static void listen(\Closure $callback)
 *
 */
class Log
{
    use Singleton;

    private $logInfo = [];
    private $message = '';

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function appendRequest(string $url, array $param, string $method)
    {
        $this->logInfo['request'][] = [
            "url" => $url,
            "param" => $param,
            "method" => $method,
            "request_time" => microtime(true)
        ];
    }

    public function appendResponse(string $url, $response, $status = '200')
    {
        $this->logInfo['response'][] = [
            "url" => $url,
            "response" => $response,
            "http_status" => $status,
            "response_time" => microtime(true)
        ];
    }

    public function appendInfo(string $key, $content)
    {
        $this->logInfo[$key][] = [
            "info_time" => microtime(true),
            "content" => $content
        ];
    }

    public function save($level = 'info')
    {
        if (!empty($this->logInfo)) {
            $this->logInfo = $this->parseInfo($this->logInfo);
            if (class_exists("\Illuminate\Log\Logger")) {
                \Illuminate\Support\Facades\Log::channel("vendor")->$level($this->message, $this->logInfo);
            }
            $this->logInfo = [];
        }
    }

    public function __destruct()
    {
        if (!empty($this->logInfo)) {
            $this->save();
        }
    }

    public static function __callStatic($name, $arguments)
    {
        if (class_exists("\Illuminate\Log\Logger")) {
            \Illuminate\Support\Facades\Log::channel("vendor")->$name(...$arguments);
        }
    }


    public function parseInfo($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $datum) {
                $data[$key] = $this->parseInfo($datum);
            }
        } elseif (is_string($data) && strlen($data) > 1000) {
            if ($data == base64_encode(base64_decode($data))) {
                $data = "base64 string, length: " . strlen($data);
            } else {
                $data = "long string, length: " . strlen($data);
            }
        }
        return $data;
    }

}

