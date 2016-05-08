<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Scanner;

use Zend\Code\Scanner\FileScanner;
use PHPUnit_Framework_TestCase as TestCase;

class MethodScannerTest extends TestCase
{
    public function testMethodScannerHasMethodInformation()
    {
        $file   = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class  = $file->getClass('ZendTest\Code\TestAsset\FooClass');
        $method = $class->getMethod('fooBarBaz');
        $this->assertEquals('fooBarBaz', $method->getName());
        $this->assertFalse($method->isAbstract());
        $this->assertTrue($method->isFinal());
        $this->assertTrue($method->isPublic());
        $this->assertFalse($method->isProtected());
        $this->assertFalse($method->isPrivate());
        $this->assertFalse($method->isStatic());
    }

    public function testMethodScannerReturnsParameters()
    {
        $file       = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class      = $file->getClass('ZendTest\Code\TestAsset\BarClass');
        $method     = $class->getMethod('three');
        $parameters = $method->getParameters();
        $this->assertInternalType('array', $parameters);
    }

    public function testMethodScannerReturnsParameterScanner()
    {
        $file   = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class  = $file->getClass('ZendTest\Code\TestAsset\BarClass');
        $method = $class->getMethod('three');
        $this->assertEquals(['o', 't', 'bbf'], $method->getParameters());
        $parameter = $method->getParameter('t');
        $this->assertInstanceOf('Zend\Code\Scanner\ParameterScanner', $parameter);
        $this->assertEquals('t', $parameter->getName());
    }

    public function testMethodScannerParsesClassNames()
    {
        $file   = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class  = $file->getClass('ZendTest\Code\TestAsset\BarClass');
        $method = $class->getMethod('five');
        $this->assertEquals(['a'], $method->getParameters());
        $parameter = $method->getParameter('a');
        $this->assertEquals('ZendTest\Code\TestAsset\AbstractClass', $parameter->getClass());
    }

    public function testMethodScannerReturnsPropertyWithNoDefault()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/BazClass.php');
        $class = $file->getClass('BazClass');
        $method = $class->getMethod('__construct');
        $this->assertTrue($method->isPublic());
    }

    public function testMethodScannerReturnsLineNumbersForMethods()
    {
        $file       = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class      = $file->getClass('ZendTest\Code\TestAsset\BarClass');
        $method     = $class->getMethod('three');
        $this->assertEquals(27, $method->getLineStart());
        $this->assertEquals(31, $method->getLineEnd());
    }

    public function testMethodScannerReturnsBodyMethods()
    {
        $file     = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class    = $file->getClass('ZendTest\Code\TestAsset\BarClass');
        $method   = $class->getMethod('three');
        $expected = "\n" . '        $x = 5 + 5;' . "\n" . '        $y = \'this string\';' . "\n    ";
        $this->assertEquals($expected, $method->getBody());
    }

    public function testMethodScannerMethodSignatureLatestOptionalParamHasParentheses()
    {
        $file       = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class      = $file->getClass('ZendTest\Code\TestAsset\BarClass');
        $method = $class->getMethod('four');
        $paramTwo = $method->getParameter(1);
        $optionalValue = $paramTwo->getDefaultValue();
        $this->assertEquals('array(array(array(\'default\')))', $optionalValue);
    }

    /**
     * @group issue-6893
     */
    public function testMethodScannerWorksWithSingleAbstractFunction()
    {
        $file = new FileScanner(__DIR__ . '/../TestAsset/AbstractClass.php');

        $class = $file->getClass('ZendTest\Code\TestAsset\AbstractClass');
        $method = $class->getMethod('helloWorld');

        $this->assertTrue($method->isAbstract());
    }
}
