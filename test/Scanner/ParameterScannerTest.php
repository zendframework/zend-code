<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Scanner;

use PHPUnit\Framework\TestCase;
use Zend\Code\Scanner\FileScanner;
use ZendTest\Code\TestAsset\BarClass;

class ParameterScannerTest extends TestCase
{
    public function testParameterScannerHasParameterInformation()
    {
        $file      = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class     = $file->getClass(BarClass::class);
        $method    = $class->getMethod('three');
        $parameter = $method->getParameter('t');
        self::assertEquals(BarClass::class, $parameter->getDeclaringClass());
        self::assertEquals('three', $parameter->getDeclaringFunction());
        self::assertEquals('t', $parameter->getName());
        self::assertEquals(2, $parameter->getPosition());
        self::assertEquals('2', $parameter->getDefaultValue());
        self::assertFalse($parameter->isArray());
        self::assertTrue($parameter->isDefaultValueAvailable());
        self::assertTrue($parameter->isOptional());
        self::assertTrue($parameter->isPassedByReference());
    }

    public function testParameterScannerHasParameterInformationForFunction()
    {
        $file      = new FileScanner(__DIR__ . '/../TestAsset/functions.php');
        $function = $file->getFunction('ZendTest\Code\TestAsset\foo_bar');
        $parameter = $function->getParameter('param2');
        $this->assertInstanceOf('Zend\Code\Scanner\ParameterScanner', $parameter);
        $this->assertEquals('param2', $parameter->getName());
        $this->assertEquals(2, $parameter->getPosition());
        $this->assertTrue($function->hasParameter('param2'));
        $this->assertTrue(in_array('param2', $function->getParameterNames()));
    }
}
