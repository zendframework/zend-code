<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

class BodyGenerator extends AbstractGenerator
{
    /**
     * @var string|null
     */
    protected $content;

    public function setContent(string $content) : self
    {
        $this->content = (string) $content;
        return $this;
    }

    public function getContent() : ?string
    {
        return $this->content;
    }

    public function generate() : ?string
    {
        return $this->getContent();
    }
}
