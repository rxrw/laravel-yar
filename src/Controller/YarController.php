<?php

/**
 * Created by PhpStorm.
 * User: li
 * Date: 2018/8/15
 * Time: 上午12:01
 */

namespace Reprover\LaravelYar\Controllers;

use Illuminate\Routing\Controller;
use Yar_Server;

class YarController extends Controller
{
    public function load($module)
    {
        $serviceName = "App\\Services\\$module";
        $server = new $serviceName();
        $service = new Yar_Server($server);
        $service->handle();
    }
}
