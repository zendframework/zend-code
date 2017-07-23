<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Reflection\DocBlock\Tag;

use function explode;
use function preg_match;
use function rtrim;

class MethodTag implements TagInterface, PhpDocTypedTagInterface
{
    /**
     * Return value type
     *
     * @var array
     */
    protected $types = [];

    /**
     * @var string
     */
    protected $methodName;

    /**
     * @var string
     */
    protected $description;

    /**
     * Is static method
     *
     * @var bool
     */
    protected $isStatic = false;

    public function getName() : string
    {
        return 'method';
    }

    /**
     * Initializer
     *
     * @param  string $tagDocblockLine
     */
    public function initialize($tagDocblockLine)
    {
        $match = [];

        if (! preg_match('#^(static[\s]+)?(.+[\s]+)?(.+\(\))[\s]*(.*)$#m', $tagDocblockLine, $match)) {
            return;
        }

        if ($match[1] !== '') {
            $this->isStatic = true;
        }

        if ($match[2] !== '') {
            $this->types = explode('|', rtrim($match[2]));
        }

        $this->methodName = $match[3];

        if ($match[4] !== '') {
            $this->description = $match[4];
        }
    }

    /**
     * @deprecated 2.0.4 use getTypes instead
     */
    public function getReturnType() : ?string
    {
        return $this->types[0] ?? null;
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function getMethodName() : string
    {
        return $this->methodName;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function isStatic() : bool
    {
        return $this->isStatic;
    }

    public function __toString() : string
    {
        return 'DocBlock Tag [ * @' . $this->getName() . ' ]' . "\n";
    }
}
