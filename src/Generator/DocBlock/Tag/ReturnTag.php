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

class ReturnTag extends AbstractTypeableTag implements TagInterface
{
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
        return 'return';
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
    public function getDatatype() : ?string
    {
        return $this->getTypesAsString();
    }

    /**
     * @return string
     */
    public function generate() : string
    {
        return '@return '
        . $this->getTypesAsString()
        . (! empty($this->description) ? ' ' . $this->description : '');
    }
}
