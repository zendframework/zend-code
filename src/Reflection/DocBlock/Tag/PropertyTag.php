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

class PropertyTag implements TagInterface, PhpDocTypedTagInterface
{
    /**
     * @var array
     */
    protected $types = [];

    /**
     * @var string
     */
    protected $propertyName;

    /**
     * @var string
     */
    protected $description;

    public function getName() : string
    {
        return 'property';
    }

    /**
     * Initializer
     *
     * @param  string $tagDocblockLine
     */
    public function initialize($tagDocblockLine)
    {
        $match = [];
        if (! preg_match('#^(.+)?(\$[\S]+)[\s]*(.*)$#m', $tagDocblockLine, $match)) {
            return;
        }

        if ($match[1] !== '') {
            $this->types = explode('|', rtrim($match[1]));
        }

        if ($match[2] !== '') {
            $this->propertyName = $match[2];
        }

        if ($match[3] !== '') {
            $this->description = $match[3];
        }
    }

    /**
     * @deprecated 2.0.4 use getTypes instead
     */
    public function getType() : ?string
    {
        return $this->types[0] ?? null;
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function getPropertyName() : ?string
    {
        return $this->propertyName;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function __toString() : string
    {
        return 'DocBlock Tag [ * @' . $this->getName() . ' ]' . "\n";
    }
}
