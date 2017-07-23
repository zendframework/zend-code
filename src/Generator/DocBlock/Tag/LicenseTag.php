<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator\DocBlock\Tag;

use Zend\Code\Generator\AbstractGenerator;
use Zend\Code\Generator\DocBlock\TagManager;
use Zend\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionTagInterface;

class LicenseTag extends AbstractGenerator implements TagInterface
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $licenseName;

    /**
     * @param string $url
     * @param string $licenseName
     */
    public function __construct($url = null, $licenseName = null)
    {
        if (! empty($url)) {
            $this->setUrl($url);
        }

        if (! empty($licenseName)) {
            $this->setLicenseName($licenseName);
        }
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
        return 'license';
    }

    public function setUrl(string $url) : self
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl() : ?string
    {
        return $this->url;
    }

    public function setLicenseName(string $name) : self
    {
        $this->licenseName = $name;
        return $this;
    }

    public function getLicenseName() : ?string
    {
        return $this->licenseName;
    }

    public function generate() : string
    {
        return '@license'
            . (! empty($this->url) ? ' ' . $this->url : '')
            . (! empty($this->licenseName) ? ' ' . $this->licenseName : '');
    }
}
