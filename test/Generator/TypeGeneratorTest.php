<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generator;

use Zend\Code\Exception\InvalidArgumentException;
use Zend\Code\Generator\GeneratorInterface;
use Zend\Code\Generator\TypeGenerator;

/**
 * @group zendframework/zend-code#29
 *
 * @requires PHP 7.0
 *
 * @covers \Zend\Code\Generator\TypeGenerator
 */
class TypeGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAGenerator()
    {
        self::assertContains(GeneratorInterface::class, class_implements(TypeGenerator::class));
    }

    /**
     * @dataProvider validTypeProvider
     *
     * @param string $typeString
     * @param string $expectedReturnType
     */
    public function testFromValidTypeString(string $typeString, string $expectedReturnType)
    {
        $generator = TypeGenerator::fromTypeString($typeString);

        self::assertSame($expectedReturnType, $generator->generate());
    }

    /**
     * @dataProvider validTypeProvider
     *
     * @param string $typeString
     * @param string $expectedReturnType
     */
    public function testStringCastFromValidTypeString(string $typeString, string $expectedReturnType)
    {
        $generator = TypeGenerator::fromTypeString($typeString);

        self::assertSame(ltrim($expectedReturnType, '\\'), (string) $generator);
    }

    /**
     * @dataProvider validClassNameProvider
     *
     * @param string $typeString
     * @param string $expectedReturnType
     */
    public function testStripsPrefixingBackslashFromClassNames(string $typeString, string $expectedReturnType)
    {
        $generator = TypeGenerator::fromTypeString('\\' . $typeString);

        self::assertSame($expectedReturnType, $generator->generate());
        self::assertSame(ltrim($expectedReturnType, '\\'), (string) $generator);
    }

    /**
     * @dataProvider invalidTypeProvider
     *
     * @param string $typeString
     */
    public function testRejectsInvalidTypeString(string $typeString)
    {
        $this->setExpectedException(InvalidArgumentException::class);

        TypeGenerator::fromTypeString($typeString);
    }

    /**
     * @dataProvider validTypeArrayProvider
     *
     * @param string $typeArray
     * @param string $expectedReturnType
     */
    public function testFromValidStringAlias(array $typeArray, string $expectedReturnType)
    {
        $generator = TypeGenerator::fromTypeArray($typeArray);

        self::assertSame($expectedReturnType, $generator->generate());
    }

    public function validTypeArrayProvider()
    {
        return [
            [['name' => 'foo', 'alias' => true], 'foo'],
            [['name' => 'foo', 'alias' => false], '\\foo'],
            [['name' => '\\foo', 'alias' => false], '\\foo'],
            [['name' => '\\foo', 'alias' => true], 'foo'],
            [['name' => 'a\\b\\c', 'alias' => false], '\\a\\b\\c'],
            [['name' => 'a\\b\\c', 'alias' => true], 'a\\b\\c'],
            [['name' => 'array', 'alias' => false], 'array'],
            [['name' => 'array', 'alias' => true], 'array'],
            [['name' => 'Array', 'alias' => false], 'array'],
            [['name' => 'Array', 'alias' => true], 'array'],
            [['name' => 'ARRAY', 'alias' => false], 'array'],
            [['name' => 'ARRAY', 'alias' => true], 'array'],
            [['name' => 'callable', 'alias' => false], 'callable'],
            [['name' => 'callable', 'alias' => true], 'callable'],
            [['name' => 'Callable', 'alias' => false], 'callable'],
            [['name' => 'Callable', 'alias' => true], 'callable'],
            [['name' => 'CALLABLE', 'alias' => false], 'callable'],
            [['name' => 'CALLABLE', 'alias' => true], 'callable'],
            [['name' => 'string', 'alias' => false], 'string'],
            [['name' => 'string', 'alias' => true], 'string'],
            [['name' => 'String', 'alias' => false], 'string'],
            [['name' => 'String', 'alias' => true], 'string'],
            [['name' => 'STRING', 'alias' => false], 'string'],
            [['name' => 'STRING', 'alias' => true], 'string'],
            [['name' => 'int', 'alias' => false], 'int'],
            [['name' => 'int', 'alias' => true], 'int'],
            [['name' => 'Int', 'alias' => false], 'int'],
            [['name' => 'Int', 'alias' => true], 'int'],
            [['name' => 'INT', 'alias' => false], 'int'],
            [['name' => 'INT', 'alias' => true], 'int'],
            [['name' => 'float', 'alias' => false], 'float'],
            [['name' => 'float', 'alias' => true], 'float'],
            [['name' => 'Float', 'alias' => false], 'float'],
            [['name' => 'Float', 'alias' => true], 'float'],
            [['name' => 'FLOAT', 'alias' => false], 'float'],
            [['name' => 'FLOAT', 'alias' => true], 'float'],
            [['name' => 'bool', 'alias' => false], 'bool'],
            [['name' => 'bool', 'alias' => true], 'bool'],
            [['name' => 'Bool', 'alias' => false], 'bool'],
            [['name' => 'Bool', 'alias' => true], 'bool'],
            [['name' => 'BOOL', 'alias' => false], 'bool'],
            [['name' => 'BOOL', 'alias' => true], 'bool'],
            [['name' => 'object', 'alias' => false], '\\object'],
            [['name' => 'object', 'alias' => true], 'object'],
            [['name' => 'Object', 'alias' => false], '\\Object'],
            [['name' => 'Object', 'alias' => true], 'Object'],
            [['name' => 'OBJECT', 'alias' => false], '\\OBJECT'],
            [['name' => 'OBJECT', 'alias' => true], 'OBJECT'],
            [['name' => 'mixed', 'alias' => false], '\\mixed'],
            [['name' => 'mixed', 'alias' => true], 'mixed'],
            [['name' => 'Mixed', 'alias' => false], '\\Mixed'],
            [['name' => 'Mixed', 'alias' => true], 'Mixed'],
            [['name' => 'MIXED', 'alias' => false], '\\MIXED'],
            [['name' => 'MIXED', 'alias' => true], 'MIXED'],
            [['name' => 'resource', 'alias' => false], '\\resource'],
            [['name' => 'resource', 'alias' => true], 'resource'],
            [['name' => 'Resource', 'alias' => false], '\\Resource'],
            [['name' => 'Resource', 'alias' => true], 'Resource'],
            [['name' => 'RESOURCE', 'alias' => false], '\\RESOURCE'],
            [['name' => 'RESOURCE', 'alias' => true], 'RESOURCE'],
            [['name' => 'foo_bar', 'alias' => false], '\\foo_bar'],
            [['name' => 'foo_bar', 'alias' => true], 'foo_bar'],
        ];
    }

    /**
     * @return string[][]
     */
    public function validTypeProvider()
    {
        return [
            ['foo', '\\foo'],
            ['foo', '\\foo'],
            ['foo1', '\\foo1'],
            ['foo\\bar', '\\foo\\bar'],
            ['a\\b\\c', '\\a\\b\\c'],
            ['foo\\bar\\baz', '\\foo\\bar\\baz'],
            ['foo\\bar\\baz1', '\\foo\\bar\\baz1'],
            ['FOO', '\\FOO'],
            ['FOO1', '\\FOO1'],
            ['array', 'array'],
            ['Array', 'array'],
            ['ARRAY', 'array'],
            ['callable', 'callable'],
            ['Callable', 'callable'],
            ['CALLABLE', 'callable'],
            ['string', 'string'],
            ['String', 'string'],
            ['STRING', 'string'],
            ['int', 'int'],
            ['Int', 'int'],
            ['INT', 'int'],
            ['float', 'float'],
            ['Float', 'float'],
            ['FLOAT', 'float'],
            ['bool', 'bool'],
            ['Bool', 'bool'],
            ['BOOL', 'bool'],
            ['object', '\\object'],
            ['Object', '\\Object'],
            ['OBJECT', '\\OBJECT'],
            ['mixed', '\\mixed'],
            ['Mixed', '\\Mixed'],
            ['MIXED', '\\MIXED'],
            ['resource', '\\resource'],
            ['Resource', '\\Resource'],
            ['RESOURCE', '\\RESOURCE'],
            ['foo_bar', '\\foo_bar'],
        ];
    }

    /**
     * Valid class names - just the same as validTypeProvider, but with only those elements prefixed by '\\'
     *
     * @return string[][]
     */
    public function validClassNameProvider()
    {
        return array_filter(
            $this->validTypeProvider(),
            function (array $pair) {
                return 0 === strpos($pair[1], '\\');
            }
        );
    }

    /**
     * @return string[][]
     */
    public function invalidTypeProvider()
    {
        return [
            [''],
            ['\\'],
            ['\\\\'],
            ['\\\\foo'],
            ['1'],
            ['\\1'],
            ['\\1\\2'],
            ['foo\\1'],
            ['foo\\bar\\1'],
            ['1foo'],
            ['foo\\1foo'],
            ['*'],
            ["\0"],
            ['\\array'],
            ['\\Array'],
            ['\\ARRAY'],
            ['\\callable'],
            ['\\Callable'],
            ['\\CALLABLE'],
            ['\\string'],
            ['\\String'],
            ['\\STRING'],
            ['\\int'],
            ['\\Int'],
            ['\\INT'],
            ['\\float'],
            ['\\Float'],
            ['\\FLOAT'],
            ['\\bool'],
            ['\\Bool'],
            ['\\BOOL'],
        ];
    }
}
