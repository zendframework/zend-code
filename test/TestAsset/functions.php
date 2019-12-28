<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\TestAsset;

/**
 * fun document
 *
 * @param $param
 * @param bool $param2
 * @return string
 */
function foo_bar($param, $param2=true)
{
    if(true){$abc='default+';}
    $abc.=$param.$param2."abc'\"";
    $fun=function(){};
    return $abc;
}

function bar_foo()
{
}
