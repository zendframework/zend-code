<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Reflection;

use Zend\Code\Reflection;
use ZendTest\Code\TestAsset\ClassTypeHintedClass;
use ZendTest\Code\TestAsset\DocBlockOnlyHintsClass;
use ZendTest\Code\TestAsset\InternalHintsClass;

/**
 * @group Zend_Reflection
 * @group Zend_Reflection_Parameter
 */
class ParameterReflectionTest extends \PHPUnit_Framework_TestCase
{
    public function testDeclaringClassReturn()
    {
        $parameter = new Reflection\ParameterReflection(
            ['ZendTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'],
            0
        );
        $this->assertInstanceOf('Zend\Code\Reflection\ClassReflection', $parameter->getDeclaringClass());
    }

    public function testClassReturnNoClassGivenReturnsNull()
    {
        $parameter = new Reflection\ParameterReflection(
            ['ZendTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'],
            'param1'
        );
        $this->assertNull($parameter->getClass());
    }

    public function testClassReturn()
    {
        $parameter = new Reflection\ParameterReflection(
            ['ZendTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'],
            'param2'
        );
        $this->assertInstanceOf('Zend\Code\Reflection\ClassReflection', $parameter->getClass());
    }

    /**
     * @dataProvider paramType
     *
     * @param string $param
     * @param string $type
     */
    public function testTypeReturn($param, $type)
    {
        $parameter = new Reflection\ParameterReflection(
            ['ZendTest\Code\Reflection\TestAsset\TestSampleClass5', 'doSomething'],
            $param
        );
        $this->assertEquals($type, $parameter->detectType());
    }

    public function testCallableTypeHint()
    {
        $parameter = new Reflection\ParameterReflection(
            ['ZendTest\Code\Reflection\TestAsset\CallableTypeHintClass', 'foo'],
            'bar'
        );
        $this->assertEquals('callable', $parameter->detectType());
    }

    public function paramType()
    {
        return [
            ['one', 'int'],
            ['two', 'int'],
            ['three', 'string'],
            ['array', 'array'],
            ['class', 'ZendTest\Code\Reflection\TestAsset\TestSampleClass'],
        ];
    }

    /**
     * @group zendframework/zend-code#29
     *
     * @requires PHP 7.0
     *
     * @dataProvider reflectionHints
     *
     * @param string $className
     * @param string $methodName
     * @param string $parameterName
     * @param string $expectedType
     */
    public function testGetType($className, $methodName, $parameterName, $expectedType)
    {
        $reflection = new Reflection\ParameterReflection(
            [$className, $methodName],
            $parameterName
        );

        $type = $reflection->getType();

        self::assertInstanceOf(\ReflectionType::class, $type);
        self::assertSame($expectedType, (string) $type);
    }

    /**
     * @group zendframework/zend-code#29
     *
     * @requires PHP 7.0
     *
     * @dataProvider reflectionHints
     *
     * @param string $className
     * @param string $methodName
     * @param string $parameterName
     * @param string $expectedType
     */
    public function testDetectType($className, $methodName, $parameterName, $expectedType)
    {
        $reflection = new Reflection\ParameterReflection(
            [$className, $methodName],
            $parameterName
        );

        // following is just due to an incompatibility between this test method and `testGetType`
        if ('self' === $expectedType) {
            $expectedType = $className;
        }

        self::assertSame($expectedType, $reflection->detectType());
    }

    /**
     * @return string[][]
     */
    public function reflectionHints()
    {
        return [
            [InternalHintsClass::class, 'arrayParameter', 'foo', 'array'],
            [InternalHintsClass::class, 'callableParameter', 'foo', 'callable'],
            [InternalHintsClass::class, 'intParameter', 'foo', 'int'],
            [InternalHintsClass::class, 'floatParameter', 'foo', 'float'],
            [InternalHintsClass::class, 'stringParameter', 'foo', 'string'],
            [InternalHintsClass::class, 'boolParameter', 'foo', 'bool'],
            [ClassTypeHintedClass::class, 'selfParameter', 'foo', 'self'],
            [ClassTypeHintedClass::class, 'classParameter', 'foo', ClassTypeHintedClass::class],
            [ClassTypeHintedClass::class, 'otherClassParameter', 'foo', InternalHintsClass::class],
            [ClassTypeHintedClass::class, 'closureParameter', 'foo', \Closure::class],
            [ClassTypeHintedClass::class, 'importedClosureParameter', 'foo', \Closure::class],
        ];
    }

    /**
     * @group zendframework/zend-code#29
     *
     * @requires PHP 7.0
     *
     * @dataProvider docBlockHints
     *
     * @param string $className
     * @param string $methodName
     * @param string $parameterName
     */
    public function testGetTypeWithDocBlockOnlyTypes($className, $methodName, $parameterName)
    {
        $reflection = new Reflection\ParameterReflection(
            [$className, $methodName],
            $parameterName
        );

        self::assertNull($reflection->getType());
    }

    /**
     * @group zendframework/zend-code#29
     *
     * @dataProvider docBlockHints
     *
     * @param string $className
     * @param string $methodName
     * @param string $parameterName
     * @param string $expectedType
     */
    public function testDetectTypeWithDocBlockOnlyTypes($className, $methodName, $parameterName, $expectedType)
    {
        $reflection = new Reflection\ParameterReflection(
            [$className, $methodName],
            $parameterName
        );

        self::assertSame($expectedType, $reflection->detectType());
    }

    /**
     * @return string[][]
     */
    public function docBlockHints()
    {
        return [
            [DocBlockOnlyHintsClass::class, 'arrayParameter', 'foo', 'array'],
            [DocBlockOnlyHintsClass::class, 'callableParameter', 'foo', 'callable'],
            [DocBlockOnlyHintsClass::class, 'intParameter', 'foo', 'int'],
            [DocBlockOnlyHintsClass::class, 'floatParameter', 'foo', 'float'],
            [DocBlockOnlyHintsClass::class, 'stringParameter', 'foo', 'string'],
            [DocBlockOnlyHintsClass::class, 'boolParameter', 'foo', 'bool'],
            [DocBlockOnlyHintsClass::class, 'selfParameter', 'foo', 'self'],
            [DocBlockOnlyHintsClass::class, 'classParameter', 'foo', 'DocBlockOnlyHintsClass'],
            [DocBlockOnlyHintsClass::class, 'otherClassParameter', 'foo', 'InternalHintsClass'],
        ];
    }
}
