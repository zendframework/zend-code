<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Reflection;

use PHPUnit\Framework\TestCase;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\MethodReflection;
use Zend\Code\Reflection\ParameterReflection;
use Zend\Code\Scanner\CachingFileScanner;
use ZendTest\Code\Reflection\TestAsset\InjectableMethodReflection;

use function array_shift;
use function trim;
use function uniqid;

/**
 * @group      Zend_Reflection
 * @group      Zend_Reflection_Method
 */
class MethodReflectionTest extends TestCase
{
    public function testDeclaringClassReturn()
    {
        $method = new MethodReflection(TestAsset\TestSampleClass2::class, 'getProp1');
        self::assertInstanceOf(ClassReflection::class, $method->getDeclaringClass());
    }

    public function testParemeterReturn()
    {
        $method = new MethodReflection(TestAsset\TestSampleClass2::class, 'getProp2');
        $parameters = $method->getParameters();
        self::assertCount(2, $parameters);
        self::assertInstanceOf(ParameterReflection::class, array_shift($parameters));
    }

    public function testStartLine()
    {
        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass5::class, 'doSomething');

        self::assertEquals(37, $reflectionMethod->getStartLine());
        self::assertEquals(21, $reflectionMethod->getStartLine(true));
    }

    public function testInternalFunctionBodyReturn()
    {
        $reflectionMethod = new MethodReflection('DOMDocument', 'validate');
        self::assertEmpty($reflectionMethod->getBody());
    }

    public function testGetBodyReturnsCorrectBody()
    {
        $body = '
        //we need a multi-line method body.
        $assigned = 1;
        $alsoAssigined = 2;
        return \'mixedValue\';';

        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass6::class, 'doSomething');
        self::assertEquals($body, $reflectionMethod->getBody());

        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'doSomething');
        $body = $reflectionMethod->getBody();
        self::assertEquals(trim($body), "return 'doSomething';");

        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'doSomethingElse');
        $body = $reflectionMethod->getBody();
        self::assertEquals(trim($body), "return 'doSomethingElse';");

        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'doSomethingAgain');
        $body = $reflectionMethod->getBody();
        self::assertEquals(
            trim($body),
            "\$closure = function(\$foo) { return \$foo; };\n\n        return 'doSomethingAgain';"
        );

        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'doStaticSomething');
        $body = $reflectionMethod->getBody();
        self::assertEquals(trim($body), "return 'doStaticSomething';");

        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'inline1');
        $body = $reflectionMethod->getBody();
        self::assertEquals(trim($body), "return 'inline1';");

        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'inline2');
        $body = $reflectionMethod->getBody();
        self::assertEquals(trim($body), "return 'inline2';");

        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'inline3');
        $body = $reflectionMethod->getBody();
        self::assertEquals(trim($body), "return 'inline3';");

        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'emptyFunction');
        $body = $reflectionMethod->getBody();
        self::assertEquals(trim($body), '');

        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'visibility');
        $body = $reflectionMethod->getBody();
        self::assertEquals(trim($body), "return 'visibility';");
    }

    public function testInternalMethodContentsReturn()
    {
        $reflectionMethod = new MethodReflection('DOMDocument', 'validate');
        self::assertEquals('', $reflectionMethod->getContents());
    }

    /**
     * @group 6275
     */
    public function testMethodContentsReturnWithoutDocBlock()
    {
        $contents = <<<CONTENTS
    public function doSomething()
    {
        return 'doSomething';
    }
CONTENTS;
        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'doSomething');
        self::assertEquals($contents, $reflectionMethod->getContents(false));

        $contents = '    public function doSomethingElse($one, $two = 2, $three = \'three\')'
            . ' { return \'doSomethingElse\'; }';
        $reflectionMethod = new MethodReflection(
            TestAsset\TestSampleClass11::class,
            'doSomethingElse'
        );
        self::assertEquals($contents, $reflectionMethod->getContents(false));

        $contents = <<<'CONTENTS'
    public function doSomethingAgain()
    {
        $closure = function($foo) { return $foo; };

        return 'doSomethingAgain';
    }
CONTENTS;
        $reflectionMethod = new MethodReflection(
            TestAsset\TestSampleClass11::class,
            'doSomethingAgain'
        );
        self::assertEquals($contents, $reflectionMethod->getContents(false));

        $contents = '    public function inline1() { return \'inline1\'; }';
        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'inline1');
        self::assertEquals($contents, $reflectionMethod->getContents(false));

        $contents = ' public function inline2() { return \'inline2\'; }';
        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'inline2');
        self::assertEquals($contents, $reflectionMethod->getContents(false));

        $contents = ' public function inline3() { return \'inline3\'; }';
        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'inline3');
        self::assertEquals($contents, $reflectionMethod->getContents(false));

        $contents = <<<'CONTENTS'
    public function visibility()
    {
        return 'visibility';
    }
CONTENTS;
        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'visibility');
        self::assertEquals($contents, $reflectionMethod->getContents(false));
    }

    public function testFunctionContentsReturnWithDocBlock()
    {
        $contents = <<<'CONTENTS'
/**
     * Doc block doSomething
     * @return string
     */
    public function doSomething()
    {
        return 'doSomething';
    }
CONTENTS;
        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'doSomething');
        self::assertEquals($contents, $reflectionMethod->getContents(true));
        self::assertEquals($contents, $reflectionMethod->getContents());

                $contents = <<<'CONTENTS'
/**
     * Awesome doc block
     */
    public function emptyFunction() {}
CONTENTS;
        $reflectionMethod = new MethodReflection(
            TestAsset\TestSampleClass11::class,
            'emptyFunction'
        );
        self::assertEquals($contents, $reflectionMethod->getContents(true));
    }

    public function testGetPrototypeMethod()
    {
        $reflectionMethod = new MethodReflection(
            TestAsset\TestSampleClass10::class,
            'doSomethingElse'
        );
        $prototype = [
            'namespace' => 'ZendTest\Code\Reflection\TestAsset',
            'class' => 'TestSampleClass10',
            'name' => 'doSomethingElse',
            'visibility' => 'public',
            'return' => 'int',
            'arguments' => [
                'one' => [
                    'type'     => 'int',
                    'required' => true,
                    'by_ref'   => false,
                    'default'  => null,
                ],
                'two' => [
                    'type'     => 'int',
                    'required' => false,
                    'by_ref'   => false,
                    'default'  => 2,
                ],
                'three' => [
                    'type'     => 'string',
                    'required' => false,
                    'by_ref'   => false,
                    'default'  => 'three',
                ],
            ],
        ];
        self::assertEquals($prototype, $reflectionMethod->getPrototype());
        self::assertEquals(
            'public int doSomethingElse(int $one, int $two = 2, string $three = \'three\')',
            $reflectionMethod->getPrototype(MethodReflection::PROTOTYPE_AS_STRING)
        );

        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass2::class, 'getProp2');
        $prototype = [
            'namespace' => 'ZendTest\Code\Reflection\TestAsset',
            'class' => 'TestSampleClass2',
            'name' => 'getProp2',
            'visibility' => 'public',
            'return' => 'mixed',
            'arguments' => [
                'param1' => [
                    'type'     => '',
                    'required' => true,
                    'by_ref'   => false,
                    'default'  => null,
                ],
                'param2' => [
                    'type'     => TestAsset\TestSampleClass::class,
                    'required' => true,
                    'by_ref'   => false,
                    'default'  => null,
                ],
            ],
        ];
        self::assertEquals($prototype, $reflectionMethod->getPrototype());
        self::assertEquals(
            'public mixed getProp2($param1, ZendTest\Code\Reflection\TestAsset\TestSampleClass $param2)',
            $reflectionMethod->getPrototype(MethodReflection::PROTOTYPE_AS_STRING)
        );

        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass12::class, 'doSomething');
        $prototype = [
            'namespace' => 'ZendTest\Code\Reflection\TestAsset',
            'class' => 'TestSampleClass12',
            'name' => 'doSomething',
            'visibility' => 'protected',
            'return' => 'string',
            'arguments' => [
                'one' => [
                    'type'     => 'int',
                    'required' => true,
                    'by_ref'   => true,
                    'default'  => null,
                ],
                'two' => [
                    'type'     => 'int',
                    'required' => true,
                    'by_ref'   => false,
                    'default'  => null,
                ],
            ],
        ];
        self::assertEquals($prototype, $reflectionMethod->getPrototype());
        self::assertEquals(
            'protected string doSomething(int &$one, int $two)',
            $reflectionMethod->getPrototype(MethodReflection::PROTOTYPE_AS_STRING)
        );
    }

    public function testGetAnnotationsWithNoNameInformations()
    {
        $reflectionMethod = new InjectableMethodReflection(
            // TestSampleClass5 has the annotations required to get to the
            // right point in the getAnnotations method.
            TestAsset\TestSampleClass5::class,
            'doSomething'
        );

        $annotationManager = new AnnotationManager();

        $fileScanner = $this->getMockBuilder(CachingFileScanner::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $reflectionMethod->setFileScanner($fileScanner);

        $fileScanner->expects($this->any())
                    ->method('getClassNameInformation')
                    ->will($this->returnValue(false));

        self::assertFalse($reflectionMethod->getAnnotations($annotationManager));
    }

    /**
     * @group 5062
     */
    public function testGetContentsWithCoreClass()
    {
        $reflectionMethod = new MethodReflection('DateTime', 'format');
        self::assertEquals('', $reflectionMethod->getContents(false));
    }

    public function testGetContentsReturnsEmptyContentsOnEvaldCode()
    {
        $className = uniqid('MethodReflectionTestGenerated');

        eval('name' . 'space ' . __NAMESPACE__ . '; cla' . 'ss ' . $className . '{fun' . 'ction foo(){}}');

        $reflectionMethod = new MethodReflection(__NAMESPACE__ . '\\' . $className, 'foo');

        self::assertSame('', $reflectionMethod->getContents());
        self::assertSame('', $reflectionMethod->getBody());
    }

    public function testGetContentsReturnsEmptyContentsOnInternalCode()
    {
        $reflectionMethod = new MethodReflection('ReflectionClass', 'getName');
        self::assertSame('', $reflectionMethod->getContents());
    }

    /**
     * @group 6275
     */
    public function testCodeGetContentsDoesNotThrowExceptionOnDocBlock()
    {

        $contents = <<<'CONTENTS'
    function getCacheKey() {
        $args = func_get_args();

        $cacheKey = '';

        foreach($args as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $argElement) {
                    $cacheKey = hash('sha256', $cacheKey.$argElement);
                }
            }
            else {
                $cacheKey = hash('sha256', $cacheKey.$arg);
            }
            //blah
        }
 
        return $cacheKey;
    }
CONTENTS;

        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, 'getCacheKey');
        self::assertEquals($contents, $reflectionMethod->getContents(false));
    }

    /**
     * @group 6275
     */
    public function testCodeGetBodyReturnsEmptyWithCommentedFunction()
    {
        $this->expectException('ReflectionException');
        $reflectionMethod = new MethodReflection(TestAsset\TestSampleClass11::class, '__prototype');
        $reflectionMethod->getBody();
    }

    /**
     * @group 6620
     */
    public function testCanParseClassBodyWhenUsingTrait()
    {
        require_once __DIR__ .'/TestAsset/TestTraitClass1.php';
        require_once __DIR__. '/TestAsset/TestTraitClass2.php';
        // $method = new \Zend\Code\Reflection\ClassReflection('\FooClass');
        // $traits = current($method->getTraits());
        $method = new MethodReflection('FooClass', 'getDummy');
        self::assertEquals(trim($method->getBody()), 'return $this->dummy;');
    }
}
