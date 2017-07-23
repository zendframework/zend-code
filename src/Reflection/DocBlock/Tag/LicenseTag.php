<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Reflection\DocBlock\Tag;

use function preg_match;
use function trim;

class LicenseTag implements TagInterface
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $licenseName;

    public function getName() : string
    {
        return 'license';
    }

    /**
     * @param  string $tagDocblockLine
     */
    public function initialize($tagDocblockLine) : void
    {
        $match = [];

        if (! preg_match('#^([\S]*)(?:\s+(.*))?$#m', $tagDocblockLine, $match)) {
            return;
        }

        if ($match[1] !== '') {
            $this->url = trim($match[1]);
        }

        if (isset($match[2]) && $match[2] !== '') {
            $this->licenseName = $match[2];
        }
    }

    public function getUrl() : ?string
    {
        return $this->url;
    }

    public function getLicenseName() : ?string
    {
        return $this->licenseName;
    }

    public function __toString() : string
    {
        return 'DocBlock Tag [ * @' . $this->getName() . ' ]' . "\n";
    }
}
