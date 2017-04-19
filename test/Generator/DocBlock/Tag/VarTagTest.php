<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generator\DocBlock\Tag;

use Zend\Code\Generator\DocBlock\Tag\VarTag;
use Zend\Code\Generator\DocBlock\TagManager;
use Zend\Code\Reflection\DocBlockReflection;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
 */
class VarTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VarTag
     */
    protected $tag;
    /**
     * @var TagManager
     */
    protected $tagmanager;

    public function setUp()
    {
        $this->tag = new VarTag();
        $this->tagmanager = new TagManager();
        $this->tagmanager->initializeDefaultTags();
    }

    public function tearDown()
    {
        $this->tag = null;
        $this->tagmanager = null;
    }

    public function testGetterAndSetterPersistValue()
    {
        $this->tag->setVariableName('variable');
        $this->assertEquals('variable', $this->tag->getVariableName());
    }


    public function testGetterForVariableNameTrimsCorrectly()
    {
        $this->tag->setVariableName('$variable$');
        $this->assertEquals('variable$', $this->tag->getVariableName());
    }

    public function testNameIsCorrect()
    {
        $this->assertEquals('var', $this->tag->getName());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setVariableName('variable');
        $this->tag->setTypes('string[]');
        $this->tag->setDescription('description');
        $this->assertEquals('@var string[] $variable description', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'variableName' => 'foo',
            'types' => ['string'],
            'description' => 'description'
        ]);
        $tagWithOptionsFromConstructor = new VarTag('foo', ['string'], 'description');
        $this->assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @var int $foo description');
        $reflectionTag = $docreflection->getTag('var');

        /** @var VarTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        $this->assertInstanceOf('Zend\Code\Generator\DocBlock\Tag\VarTag', $tag);
        $this->assertEquals('foo', $tag->getVariableName());
        $this->assertEquals('description', $tag->getDescription());
        $this->assertEquals('int', $tag->getTypesAsString());
    }
}
