<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Scanner;

use Zend\Code\Exception;

class AggregateDirectoryScanner extends DirectoryScanner
{
    /**
     * @param  bool $returnScannerClass
     * @todo not implemented
     */
    public function getNamespaces($returnScannerClass = false)
    {
        // @todo
    }

    public function getIncludes($returnScannerClass = false)
    {
    }

    public function getClasses(bool $returnScannerClass = false, bool $returnDerivedScannerClass = false) : array
    {
        $classes = [];
        foreach ($this->directories as $scanner) {
            $classes += $scanner->getClasses();
        }
        if ($returnScannerClass) {
            foreach ($classes as $index => $class) {
                $classes[$index] = $this->getClass($class, $returnScannerClass, $returnDerivedScannerClass);
            }
        }

        return $classes;
    }

    public function hasClass(string $class) : bool
    {
        foreach ($this->directories as $scanner) {
            if ($scanner->hasClass($class)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Exception\RuntimeException
     */
    public function getClass(
        string $class,
        bool $returnScannerClass = true,
        bool $returnDerivedScannerClass = false
    ) : ClassScanner {
        $classScanner = null;

        foreach ($this->directories as $scanner) {
            if ($scanner->hasClass($class)) {
                $classScanner = $scanner->getClass($class);
            }
        }

        if (! $classScanner) {
            throw new Exception\RuntimeException('Class by that name was not found.');
        }

        return new DerivedClassScanner($classScanner, $this);
    }

    public function getFunctions(bool $returnScannerClass = false)
    {
        $this->scan();

        if (! $returnScannerClass) {
            $functions = [];
            foreach ($this->infos as $info) {
                if ($info['type'] === 'function') {
                    $functions[] = $info['name'];
                }
            }

            return $functions;
        }
        // @todo
    }
}
