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
use function implode;
use function preg_match;

class ThrowsTag implements TagInterface, PhpDocTypedTagInterface
{
    /**
     * @var array
     */
    protected $types = [];

    /**
     * @var string
     */
    protected $description;

    public function getName() : string
    {
        return 'throws';
    }

    /**
     * @param  string $tagDocBlockLine
     */
    public function initialize($tagDocBlockLine) : void
    {
        $matches = [];
        preg_match('#([\w|\\\]+)(?:\s+(.*))?#', $tagDocBlockLine, $matches);

        $this->types = explode('|', $matches[1]);

        if (isset($matches[2])) {
            $this->description = $matches[2];
        }
    }

    /**
     * Get return variable type
     *
     * @deprecated 2.0.4 use getTypes instead
     */
    public function getType() : string
    {
        return implode('|', $this->getTypes());
    }

    public function getTypes() : array
    {
        return $this->types;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }
}
