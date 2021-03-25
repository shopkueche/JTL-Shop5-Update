<?php

namespace JTL\phpQuery;

use Exception;

/**
 * Plugins static namespace class.
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package phpQuery
 * @todo move plugin methods here (as statics)
 */
class phpQueryPlugins
{
    /**
     * @param $method
     * @param $args
     * @return mixed|phpQueryPlugins|void
     * @throws Exception
     */
    public function __call($method, $args)
    {
        if (isset(phpQuery::$extendStaticMethods[$method])) {
            \call_user_func_array(phpQuery::$extendStaticMethods[$method], $args);
        } else {
            if (isset(phpQuery::$pluginsStaticMethods[$method])) {
                $class     = phpQuery::$pluginsStaticMethods[$method];
                $realClass = "phpQueryPlugin_$class";
                $return    = \call_user_func_array([$realClass, $method], $args);

                return $return ?? $this;
            }
            throw new Exception("Method '{$method}' doesnt exist");
        }
    }
}
