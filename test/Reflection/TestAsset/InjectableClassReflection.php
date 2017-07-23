<?php

namespace ZendTest\Code\Reflection\TestAsset;

use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Scanner\FileScanner;

class InjectableClassReflection extends ClassReflection
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
