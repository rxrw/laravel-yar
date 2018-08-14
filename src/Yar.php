<?php
/**
 * Yar 客户端程序
 * User: li
 * Date: 2018/8/14
 * Time: 下午10:47
 */

namespace Reprover\LaravelYar;
use Yar_Client;

class Yar
{

    public static function call($mapName, $parameters){
        $mapConfig = config("yar-map.".$mapName);
        $module = $mapConfig['module'];
        $serviceConfig = config("yar-services.".$module);
        $url = $serviceConfig['path'] . $serviceConfig['services'][$mapConfig['service']];
        $yarClient = new Yar_Client($url);
        if(isset($mapConfig['connect_timeout']))$yarClient->setOpt(YAR_OPT_CONNECT_TIMEOUT,$mapConfig['connect_timeout']);
        if(isset($mapConfig['read_timeout']))$yarClient->setOpt(YAR_OPT_TIMEOUT,$mapConfig['connect_timeout']);

        try{
            $res = $yarClient->{$mapConfig['method']}($parameters);
        }catch (Yar_Client_Exception $e){
            throw $e;
        }
        return $res;
    }

}