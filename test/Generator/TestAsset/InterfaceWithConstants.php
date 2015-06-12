<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace ZendTest\Code\Generator\TestAsset;
interface InterfaceWithConstants extends \Iterator, \Countable
{
    const FOO = 1;
    const BAR = '2';
    const FOOBAR = 0x20;

    /**
     * Enter description here...
     *
     * @return bool
     */
    public function someMethod();
}