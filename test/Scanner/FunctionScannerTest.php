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
use Zend\Code\Scanner\ParameterScanner;

class FunctionScannerTest extends TestCase
{
    public function testFunctionScannerHasMethodInformation()
    {
        $file   = new FileScanner(__DIR__ . '/../TestAsset/functions.php');
        $function = $file->getFunction('ZendTest\Code\TestAsset\foo_bar');
        self::assertEquals('ZendTest\Code\TestAsset\foo_bar', $function->getName());
        self::assertEquals('foo_bar', $function->getShortName());
    }

    public function testFunctionScannerReturnsFunctionsAsNoNamespace()
    {
        $file      = new FileScanner(__DIR__ . '/../TestAsset/functions-no-namespace.php');
        self::assertEquals(['foo_bar', 'bar_foo'], $file->getFunctionNames());
    }

    public function testFunctionScannerReturnsParameterScanner()
    {
        $file       = new FileScanner(__DIR__ . '/../TestAsset/functions.php');
        $function = $file->getFunction('ZendTest\Code\TestAsset\foo_bar');
        $parameters = $function->getParameters();
        self::assertIsArray($parameters);
        foreach ($parameters as $parameter) {
            self::assertInstanceOf(ParameterScanner::class, $parameter);
        }
    }

    public function testFunctionScannerReturnsParameterNames()
    {
        $file       = new FileScanner(__DIR__ . '/../TestAsset/functions.php');
        $function = $file->getFunction('ZendTest\Code\TestAsset\foo_bar');
        self::assertEquals(['param', 'param2'], $function->getParameterNames());

        $parameter = $function->getParameter('param2');
        self::assertInstanceOf(ParameterScanner::class, $parameter);
        self::assertEquals('param2', $parameter->getName());
    }

    public function testFunctionScannerReturnsLineNumbersForMethods()
    {
        $file       = new FileScanner(__DIR__ . '/../TestAsset/functions.php');
        $function = $file->getFunction('ZendTest\Code\TestAsset\foo_bar');
        self::assertEquals(12, $function->getLineStart());
        self::assertEquals(25, $function->getLineEnd());

        $function = $file->getFunction('ZendTest\Code\TestAsset\five');
        self::assertEquals(38, $function->getLineStart());
        self::assertEquals(41, $function->getLineEnd());
    }

    public function testFunctionScannerReturnsBody()
    {
        $file     = new FileScanner(__DIR__ . '/../TestAsset/functions.php');
        $function = $file->getFunction('ZendTest\Code\TestAsset\bar_foo');
        $expected = "\n" . '    $x = 5 + 5;' . "\n" . '    $y = \'this string\';' . "\n";
        self::assertEquals($expected, $function->getBody());
    }

    public function testFunctionScannerMethodSignatureLatestOptionalParamHasParentheses()
    {
        $file       = new FileScanner(__DIR__ . '/../TestAsset/functions.php');
        $function = $file->getFunction('ZendTest\Code\TestAsset\four');
        $paramTwo = $function->getParameter(1);
        $optionalValue = $paramTwo->getDefaultValue();
        self::assertEquals('array([array(\'default\')])', $optionalValue);
    }

    public function testFunctionScannerSetVisibilityThrowsInvalidArgumentException()
    {
        $file       = new FileScanner(__DIR__ . '/../TestAsset/functions.php');
        $functionScanner = $file->getFunction('ZendTest\Code\TestAsset\four');

        $this->expectException('\Zend\Code\Exception\InvalidArgumentException');
        $functionScanner->getParameter('not parameter');
    }
}
