<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator\DocBlock\Tag;

use function rtrim;

class MethodTag extends AbstractTypeableTag implements TagInterface
{
    /**
     * @var string
     */
    protected $methodName;

    /**
     * @var bool
     */
    protected $isStatic = false;

    /**
     * @param string $methodName
     * @param array $types
     * @param string $description
     * @param bool $isStatic
     */
    public function __construct($methodName = null, $types = [], $description = null, $isStatic = false)
    {
        if (! empty($methodName)) {
            $this->setMethodName($methodName);
        }

        $this->setIsStatic((bool) $isStatic);

        parent::__construct($types, $description);
    }

    public function getName() : string
    {
        return 'method';
    }

    public function setIsStatic(bool $isStatic) : self
    {
        $this->isStatic = $isStatic;
        return $this;
    }

    public function isStatic() : bool
    {
        return $this->isStatic;
    }

    public function setMethodName(string $methodName) : self
    {
        $this->methodName = rtrim($methodName, ')(');
        return $this;
    }

    public function getMethodName() : ?string
    {
        return $this->methodName;
    }

    /**
     * @return string
     */
    public function generate() : string
    {
        return '@method'
            . ($this->isStatic ? ' static' : '')
            . (! empty($this->types) ? ' ' . $this->getTypesAsString() : '')
            . (! empty($this->methodName) ? ' ' . $this->methodName . '()' : '')
            . (! empty($this->description) ? ' ' . $this->description : '');
    }
}
