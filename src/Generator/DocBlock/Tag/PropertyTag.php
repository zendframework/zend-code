<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator\DocBlock\Tag;

use function ltrim;

class PropertyTag extends AbstractTypeableTag implements TagInterface
{
    /**
     * @var string
     */
    protected $propertyName;

    /**
     * @param string $propertyName
     * @param array $types
     * @param string $description
     */
    public function __construct($propertyName = null, $types = [], $description = null)
    {
        if (! empty($propertyName)) {
            $this->setPropertyName($propertyName);
        }

        parent::__construct($types, $description);
    }

    public function getName() : string
    {
        return 'property';
    }

    public function setPropertyName(string $propertyName) : self
    {
        $this->propertyName = ltrim($propertyName, '$');
        return $this;
    }

    public function getPropertyName() : ?string
    {
        return $this->propertyName;
    }

    public function generate() : string
    {
        $output = '@property'
            . (! empty($this->types) ? ' ' . $this->getTypesAsString() : '')
            . (! empty($this->propertyName) ? ' $' . $this->propertyName : '')
            . (! empty($this->description) ? ' ' . $this->description : '');

        return $output;
    }
}
