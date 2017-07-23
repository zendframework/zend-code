<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator\DocBlock\Tag;

use Zend\Code\Generator\DocBlock\TagManager;
use Zend\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionTagInterface;

use function ltrim;

class ParamTag extends AbstractTypeableTag implements TagInterface
{
    /**
     * @var string
     */
    protected $variableName;

    /**
     * @param string $variableName
     * @param array $types
     * @param string $description
     */
    public function __construct($variableName = null, $types = [], $description = null)
    {
        if (! empty($variableName)) {
            $this->setVariableName($variableName);
        }

        parent::__construct($types, $description);
    }

    /**
     * @deprecated Deprecated in 2.3. Use TagManager::createTagFromReflection() instead
     */
    public static function fromReflection(ReflectionTagInterface $reflectionTag) : self
    {
        $tagManager = new TagManager();
        $tagManager->initializeDefaultTags();
        return $tagManager->createTagFromReflection($reflectionTag);
    }

    public function getName() : string
    {
        return 'param';
    }

    public function setVariableName(string $variableName) : self
    {
        $this->variableName = ltrim($variableName, '$');
        return $this;
    }

    public function getVariableName() : ?string
    {
        return $this->variableName;
    }

    /**
     * @param string|string[] $datatype
     * @deprecated Deprecated in 2.3. Use setTypes() instead
     */
    public function setDatatype($datatype) : self
    {
        return $this->setTypes($datatype);
    }

    /**
     * @deprecated Deprecated in 2.3. Use getTypes() or getTypesAsString() instead
     */
    public function getDatatype() : string
    {
        return $this->getTypesAsString();
    }

    /**
     * @deprecated Deprecated in 2.3. Use setVariableName() instead
     */
    public function setParamName(string $paramName) : self
    {
        return $this->setVariableName($paramName);
    }

    /**
     * @deprecated Deprecated in 2.3. Use getVariableName() instead
     */
    public function getParamName() : ?string
    {
        return $this->getVariableName();
    }

    public function generate() : string
    {
        return '@param'
            . (! empty($this->types) ? ' ' . $this->getTypesAsString() : '')
            . (! empty($this->variableName) ? ' $' . $this->variableName : '')
            . (! empty($this->description) ? ' ' . $this->description : '');
    }
}
