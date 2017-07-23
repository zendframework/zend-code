<?php

namespace ZendTest\Code\Reflection\TestAsset;

use Zend\Code\Reflection\PropertyReflection;
use Zend\Code\Scanner\FileScanner;

class InjectablePropertyReflection extends PropertyReflection
{
    /**
     * @var FileScanner|null
     */
    protected $fileScanner;

    public function setFileScanner(FileScanner $fileScanner) : void
    {
        $this->fileScanner = $fileScanner;
    }

    protected function createFileScanner(string $filename) : FileScanner
    {
        return $this->fileScanner;
    }
}
