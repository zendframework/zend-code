<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generator;

use Zend\Code\Generator\TraitGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\ClassReflection;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
 */
class TraitGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testConstruction()
    {
        $class = new TraitGenerator();
        $this->isInstanceOf($class, 'Zend\Code\Generator\TraitGenerator');
    }

    public function testNameAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setName('TestClass');
        $this->assertEquals($classGenerator->getName(), 'TestClass');
    }

    public function testClassDocBlockAccessors()
    {
        $this->markTestIncomplete();
    }

    public function testAbstractAccessorsReturnsFalse()
    {
        $classGenerator = new TraitGenerator();
        $this->assertFalse($classGenerator->isAbstract());
        $classGenerator->setAbstract(true);
        $this->assertFalse($classGenerator->isAbstract());
    }

    public function testExtendedClassAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setExtendedClass('ExtendedClass');
        $this->assertEquals($classGenerator->getExtendedClass(), null);
    }

    public function testImplementedInterfacesAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setImplementedInterfaces(['Class1', 'Class2']);
        $this->assertEquals(count($classGenerator->getImplementedInterfaces()), 0);
    }

    public function testPropertyAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addProperties([
            'propOne',
            new PropertyGenerator('propTwo'),
        ]);

        $properties = $classGenerator->getProperties();
        $this->assertEquals(count($properties), 2);
        $this->assertInstanceOf('Zend\Code\Generator\PropertyGenerator', current($properties));

        $property = $classGenerator->getProperty('propTwo');
        $this->assertInstanceOf('Zend\Code\Generator\PropertyGenerator', $property);
        $this->assertEquals($property->getName(), 'propTwo');

        // add a new property
        $classGenerator->addProperty('prop3');
        $this->assertEquals(count($classGenerator->getProperties()), 3);
    }

    public function testSetPropertyAlreadyExistsThrowsException()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addProperty('prop3');

        $this->setExpectedException(
            'Zend\Code\Generator\Exception\InvalidArgumentException',
            'A property by name prop3 already exists in this class'
        );
        $classGenerator->addProperty('prop3');
    }

    public function testSetPropertyNoArrayOrPropertyThrowsException()
    {
        $classGenerator = new TraitGenerator();

        $this->setExpectedException(
            'Zend\Code\Generator\Exception\InvalidArgumentException',
            'Zend\Code\Generator\TraitGenerator::addProperty expects string for name'
        );
        $classGenerator->addProperty(true);
    }

    public function testMethodAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethods([
            'methodOne',
            new MethodGenerator('methodTwo'),
        ]);

        $methods = $classGenerator->getMethods();
        $this->assertEquals(count($methods), 2);
        $this->isInstanceOf(current($methods), '\Zend\Code\Generator\PhpMethod');

        $method = $classGenerator->getMethod('methodOne');
        $this->isInstanceOf($method, '\Zend\Code\Generator\PhpMethod');
        $this->assertEquals($method->getName(), 'methodOne');

        // add a new property
        $classGenerator->addMethod('methodThree');
        $this->assertEquals(count($classGenerator->getMethods()), 3);
    }

    public function testSetMethodNoMethodOrArrayThrowsException()
    {
        $classGenerator = new TraitGenerator();

        $this->setExpectedException(
            'Zend\Code\Generator\Exception\ExceptionInterface',
            'Zend\Code\Generator\TraitGenerator::addMethod expects string for name'
        );

        $classGenerator->addMethod(true);
    }

    public function testSetMethodNameAlreadyExistsThrowsException()
    {
        $methodA = new MethodGenerator();
        $methodA->setName('foo');
        $methodB = new MethodGenerator();
        $methodB->setName('foo');

        $classGenerator = new TraitGenerator();
        $classGenerator->addMethodFromGenerator($methodA);

        $this->setExpectedException(
            'Zend\Code\Generator\Exception\InvalidArgumentException',
            'A method by name foo already exists in this class.'
        );

        $classGenerator->addMethodFromGenerator($methodB);
    }

    /**
     * @group ZF-7361
     */
    public function testHasMethod()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');

        $this->assertTrue($classGenerator->hasMethod('methodOne'));
    }

    public function testRemoveMethod()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');
        $this->assertTrue($classGenerator->hasMethod('methodOne'));

        $classGenerator->removeMethod('methodOne');
        $this->assertFalse($classGenerator->hasMethod('methodOne'));
    }

    /**
     * @group ZF-7361
     */
    public function testHasProperty()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addProperty('propertyOne');

        $this->assertTrue($classGenerator->hasProperty('propertyOne'));
    }

    public function testToString()
    {
        $classGenerator = TraitGenerator::fromArray([
            'name' => 'SampleClass',
            'properties' => [
                'foo',
                ['name' => 'bar'],
            ],
            'methods' => [
                ['name' => 'baz'],
            ],
        ]);

        $expectedOutput = <<<EOS
trait SampleClass
{

    public \$foo = null;

    public \$bar = null;

    public function baz()
    {
    }


}

EOS;

        $output = $classGenerator->generate();
        $this->assertEquals($expectedOutput, $output, $output);
    }

    /**
     * @group ZF-7909
     */
    public function testClassFromReflectionThatImplementsInterfaces()
    {
        $reflClass = new ClassReflection('ZendTest\Code\Generator\TestAsset\ClassWithInterface');

        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $classGenerator->setSourceDirty(true);

        $code = $classGenerator->generate();

        $expectedClassDef = 'trait ClassWithInterface';
        $this->assertContains($expectedClassDef, $code);
    }

    /**
     * @group ZF-7909
     */
    public function testClassFromReflectionDiscardParentImplementedInterfaces()
    {
        $reflClass = new ClassReflection('ZendTest\Code\Generator\TestAsset\NewClassWithInterface');

        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $classGenerator->setSourceDirty(true);

        $code = $classGenerator->generate();

        $expectedClassDef = 'trait NewClassWithInterface';
        $this->assertContains($expectedClassDef, $code);
    }

    /**
     * @group 4988
     */
    public function testNonNamespaceClassReturnsAllMethods()
    {
        require_once __DIR__ . '/../TestAsset/NonNamespaceClass.php';

        $reflClass = new ClassReflection('ZendTest_Code_NsTest_BarClass');
        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $this->assertCount(1, $classGenerator->getMethods());
    }

    /**
     * @group ZF-9602
     */
    public function testSetextendedclassShouldIgnoreEmptyClassnameOnGenerate()
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass
            ->setName('MyClass')
            ->setExtendedClass('');

        $expected = <<<CODE
trait MyClass
{


}

CODE;
        $this->assertEquals($expected, $classGeneratorClass->generate());
    }

    /**
     * @group ZF-9602
     */
    public function testSetextendedclassShouldNotIgnoreNonEmptyClassnameOnGenerate()
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass
            ->setName('MyClass')
            ->setExtendedClass('ParentClass');

        $expected = <<<CODE
trait MyClass
{


}

CODE;
        $this->assertEquals($expected, $classGeneratorClass->generate());
    }

    /**
     * @group namespace
     */
    public function testCodeGenerationShouldTakeIntoAccountNamespacesFromReflection()
    {
        $reflClass = new ClassReflection('ZendTest\Code\Generator\TestAsset\ClassWithNamespace');
        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $this->assertEquals('ZendTest\Code\Generator\TestAsset', $classGenerator->getNamespaceName());
        $this->assertEquals('ClassWithNamespace', $classGenerator->getName());
        $expected = <<<CODE
namespace ZendTest\Code\Generator\\TestAsset;

trait ClassWithNamespace
{


}

CODE;
        $received = $classGenerator->generate();
        $this->assertEquals($expected, $received, $received);
    }

    /**
     * @group namespace
     */
    public function testSetNameShouldDetermineIfNamespaceSegmentIsPresent()
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $this->assertEquals('My\Namespaced', $classGeneratorClass->getNamespaceName());
    }

    /**
     * @group namespace
     */
    public function testPassingANamespacedClassnameShouldGenerateANamespaceDeclaration()
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        $this->assertContains('namespace My\Namespaced;', $received, $received);
    }

    /**
     * @group namespace
     */
    public function testPassingANamespacedClassnameShouldGenerateAClassnameWithoutItsNamespace()
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        $this->assertContains('trait FunClass', $received, $received);
    }

    /**
     * @group ZF2-151
     */
    public function testAddUses()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setName('My\Class');
        $classGenerator->addUse('My\First\Use\Class');
        $classGenerator->addUse('My\Second\Use\Class', 'MyAlias');
        $generated = $classGenerator->generate();

        $this->assertContains('use My\First\Use\Class;', $generated);
        $this->assertContains('use My\Second\Use\Class as MyAlias;', $generated);
    }

    /**
     * @group 4990
     */
    public function testAddOneUseTwiceOnlyAddsOne()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setName('My\Class');
        $classGenerator->addUse('My\First\Use\Class');
        $classGenerator->addUse('My\First\Use\Class');
        $generated = $classGenerator->generate();

        $this->assertCount(1, $classGenerator->getUses());

        $this->assertContains('use My\First\Use\Class;', $generated);
    }

    /**
     * @group 4990
     */
    public function testAddOneUseWithAliasTwiceOnlyAddsOne()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setName('My\Class');
        $classGenerator->addUse('My\First\Use\Class', 'MyAlias');
        $classGenerator->addUse('My\First\Use\Class', 'MyAlias');
        $generated = $classGenerator->generate();

        $this->assertCount(1, $classGenerator->getUses());

        $this->assertContains('use My\First\Use\Class as MyAlias;', $generated);
    }

    public function testCreateFromArrayWithDocBlockFromArray()
    {
        $classGenerator = TraitGenerator::fromArray([
            'name' => 'SampleClass',
            'docblock' => [
                'shortdescription' => 'foo',
            ],
        ]);

        $docBlock = $classGenerator->getDocBlock();
        $this->assertInstanceOf('Zend\Code\Generator\DocBlockGenerator', $docBlock);
    }

    public function testCreateFromArrayWithDocBlockInstance()
    {
        $classGenerator = TraitGenerator::fromArray([
            'name' => 'SampleClass',
            'docblock' => new DocBlockGenerator('foo'),
        ]);

        $docBlock = $classGenerator->getDocBlock();
        $this->assertInstanceOf('Zend\Code\Generator\DocBlockGenerator', $docBlock);
    }

    public function testExtendedClassProperies()
    {
        $reflClass = new ClassReflection('ZendTest\Code\Generator\TestAsset\ExtendedClassWithProperties');
        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $code = $classGenerator->generate();
        $this->assertContains('publicExtendedClassProperty', $code);
        $this->assertContains('protectedExtendedClassProperty', $code);
        $this->assertContains('privateExtendedClassProperty', $code);
        $this->assertNotContains('publicClassProperty', $code);
        $this->assertNotContains('protectedClassProperty', $code);
        $this->assertNotContains('privateClassProperty', $code);
    }

    public function testHasMethodInsensitive()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');

        $this->assertTrue($classGenerator->hasMethod('methodOne'));
        $this->assertTrue($classGenerator->hasMethod('MethoDonE'));
    }

    public function testRemoveMethodInsensitive()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');

        $classGenerator->removeMethod('METHODONe');
        $this->assertFalse($classGenerator->hasMethod('methodOne'));
    }

    public function testGenerateClassAndAddMethod()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setName('MyClass');
        $classGenerator->addMethod('methodOne');

        $expected = <<<CODE
trait MyClass
{

    public function methodOne()
    {
    }


}

CODE;

        $output = $classGenerator->generate();
        $this->assertEquals($expected, $output);
    }
}
