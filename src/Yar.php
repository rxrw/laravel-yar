<?php

/**
 * Yar 客户端程序
 * User: li
 * Date: 2018/8/14
 * Time: 下午10:47
 */

namespace Reprover\LaravelYar;

use Yar_Client;
use Yar_Concurrent_Client;

class Yar
{

    protected $async = false;
    protected $mapConfig = [];
    protected $module = '';
    protected $serviceConfig = [];
    protected $method = '';
    protected $url = '';
    protected $callback = '';

    public function __construct($mapName = '', $async = false)
    {
        if ($mapName) {
            $this->getResource($mapName);
        }
        if ($async) {
            $this->async();
        }
    }

    public function call(array $parameters)
    {
        $yarClient = $this->getClient();
        if (!$this->async) {
            if (isset($this->mapConfig['connect_timeout'])) {
                $yarClient->setOpt(
                    YAR_OPT_CONNECT_TIMEOUT,
                    $this->mapConfig['connect_timeout']
                );
            }

            if (isset($this->mapConfig['read_timeout'])) {
                $yarClient->setOpt(
                    YAR_OPT_TIMEOUT,
                    $this->mapConfig['connect_timeout']
                );
            }
        }

        try {
            if (!$this->async) {
                $res = $yarClient->{$this->mapConfig['method']}($parameters);
            } else {
                $res = $yarClient::call(
                    $this->url,
                    $this->method,
                    $parameters,
                    $this->callback
                );
            }
        } catch (Yar_Client_Exception $e) {
            throw $e;
        }

        return $res;
    }

    public static function asyncCall(string $mapName, array $parameters, callable $callback)
    {
        $yarClient = new self($mapName, true);
        $yarClient->setCallback($callback);
        $yarClient->call($parameters);
    }

    public static function loop()
    {
        Yar_Concurrent_Client::loop();
    }

    public static function reset()
    {
        Yar_Concurrent_Client::reset();
    }

    public function setCallback(callable $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    public function getResource($mapName)
    {
        $this->mapConfig = config("yar-map." . $mapName);
        $this->module = $this->mapConfig['module'];
        $this->serviceConfig = config("yar-services." . $this->module);
        $this->url = $this->serviceConfig['path']
            . $this->serviceConfig['services'][$this->mapConfig['service']];
        $this->method = $this->mapConfig['method'];

        return $this;
    }

    protected function getClient()
    {
        if (!$this->url) {
            throw new \Exception;
        }
        if ($this->async) {
            return new Yar_Concurrent_Client();
        } else {
            return new Yar_Client($this->url);
        }
    }

    public function async()
    {
        $this->async = true;

        return $this;
    }

    public function __callStatic($name, $arguments)
    {
        $yarClient = new self($name);
        if (isset($arguments[1]) && $arguments[1] == true) {
            $yarClient->async();
            $yarClient->setCallback($arguments[2]);
        }
        return $yarClient->call($arguments[0]);
    }

}
