<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Scanner;

use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Exception;
use Zend\Code\NameInformation;

use function file_exists;
use function md5;
use function realpath;
use function spl_object_hash;
use function sprintf;

class CachingFileScanner extends FileScanner
{
    /**
     * @var array
     */
    protected static $cache = [];

    /**
     * @var null|FileScanner
     */
    protected $fileScanner;

    /**
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(string $file, AnnotationManager $annotationManager = null)
    {
        if (! file_exists($file)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'File "%s" not found',
                $file
            ));
        }

        $file = (string) realpath($file);

        $cacheId = md5($file) . '/' . (
            $annotationManager
                ? spl_object_hash($annotationManager)
                : 'no-annotation'
            );

        if (isset(static::$cache[$cacheId])) {
            $this->fileScanner = static::$cache[$cacheId];
        } else {
            $this->fileScanner       = new FileScanner($file, $annotationManager);
            static::$cache[$cacheId] = $this->fileScanner;
        }
    }

    public static function clearCache() : void
    {
        static::$cache = [];
    }

    public function getAnnotationManager() : ?AnnotationManager
    {
        return $this->fileScanner->getAnnotationManager();
    }

    public function getFile() : string
    {
        return $this->fileScanner->getFile();
    }

    public function getDocComment() : ?string
    {
        return $this->fileScanner->getDocComment();
    }

    public function getNamespaces() : array
    {
        return $this->fileScanner->getNamespaces();
    }

    public function getUses(?string $namespace = null) : ?array
    {
        return $this->fileScanner->getUses($namespace);
    }

    /**
     * @return array
     *
     * Note: nullability on the hint is needed, because the inner object does not implement anything yet
     */
    public function getIncludes() : ?array
    {
        return $this->fileScanner->getIncludes();
    }

    /**
     * @return array
     */
    public function getClassNames() : array
    {
        return $this->fileScanner->getClassNames();
    }

    /**
     * @return array
     */
    public function getClasses() : array
    {
        return $this->fileScanner->getClasses();
    }

    /**
     * @param  int|string $className
     */
    public function getClass($className) : ClassScanner
    {
        return $this->fileScanner->getClass($className);
    }

    /**
     * @param  string $className
     * @return bool|null|NameInformation
     */
    public function getClassNameInformation($className)
    {
        return $this->fileScanner->getClassNameInformation($className);
    }

    /**
     * @return array
     */
    public function getFunctionNames() : array
    {
        return $this->fileScanner->getFunctionNames();
    }

    /**
     * @return array
     */
    public function getFunctions() : array
    {
        return $this->fileScanner->getFunctions();
    }
}
