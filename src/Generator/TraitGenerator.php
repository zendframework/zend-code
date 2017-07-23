<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

use Zend\Code\Reflection\ClassReflection;

use function str_replace;
use function strtolower;

class TraitGenerator extends ClassGenerator
{
    const OBJECT_TYPE = 'trait';

    /**
     * Build a Code Generation Php Object from a Class Reflection
     *
     * @param  ClassReflection $classReflection
     * @return TraitGenerator
     */
    public static function fromReflection(ClassReflection $classReflection) : parent
    {
        // class generator
        $cg = new static($classReflection->getName());

        $cg->setSourceContent($cg->getSourceContent());
        $cg->setSourceDirty(false);

        if ($classReflection->getDocComment() != '') {
            $cg->setDocBlock(DocBlockGenerator::fromReflection($classReflection->getDocBlock()));
        }

        // set the namespace
        if ($classReflection->inNamespace()) {
            $cg->setNamespaceName($classReflection->getNamespaceName());
        }

        $properties = [];
        foreach ($classReflection->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->getDeclaringClass()->getName() == $classReflection->getName()) {
                $properties[] = PropertyGenerator::fromReflection($reflectionProperty);
            }
        }
        $cg->addProperties($properties);

        $methods = [];
        foreach ($classReflection->getMethods() as $reflectionMethod) {
            $className = $cg->getNamespaceName()
                ? $cg->getNamespaceName() . '\\' . $cg->getName()
                : $cg->getName();
            if ($reflectionMethod->getDeclaringClass()->getName() == $className) {
                $methods[] = MethodGenerator::fromReflection($reflectionMethod);
            }
        }
        $cg->addMethods($methods);

        return $cg;
    }

    /**
     * Generate from array
     *
     * @configkey name           string        [required] Class Name
     * @configkey filegenerator  FileGenerator File generator that holds this class
     * @configkey namespacename  string        The namespace for this class
     * @configkey docblock       string        The docblock information
     * @configkey properties
     * @configkey methods
     *
     * @throws Exception\InvalidArgumentException
     * @param  array $array
     * @return TraitGenerator
     */
    public static function fromArray(array $array) : parent
    {
        if (! isset($array['name'])) {
            throw new Exception\InvalidArgumentException(
                'Class generator requires that a name is provided for this object'
            );
        }

        $cg = new static($array['name']);
        foreach ($array as $name => $value) {
            // normalize key
            switch (strtolower(str_replace(['.', '-', '_'], '', $name))) {
                case 'containingfile':
                    $cg->setContainingFileGenerator($value);
                    break;
                case 'namespacename':
                    $cg->setNamespaceName($value);
                    break;
                case 'docblock':
                    $docBlock = $value instanceof DocBlockGenerator ? $value : DocBlockGenerator::fromArray($value);
                    $cg->setDocBlock($docBlock);
                    break;
                case 'properties':
                    $cg->addProperties($value);
                    break;
                case 'methods':
                    $cg->addMethods($value);
                    break;
            }
        }

        return $cg;
    }

    public function setFlags($flags) : parent
    {
        return $this;
    }

    public function addFlag($flag) : parent
    {
        return $this;
    }

    public function removeFlag($flag) : parent
    {
        return $this;
    }

    public function setFinal(bool $isFinal) : parent
    {
        return $this;
    }

    public function setExtendedClass(?string $extendedClass) : parent
    {
        return $this;
    }

    public function setImplementedInterfaces(array $implementedInterfaces) : parent
    {
        return $this;
    }

    public function setAbstract(bool $isAbstract) : parent
    {
        return $this;
    }
}
