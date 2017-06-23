<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Scanner;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Code\Scanner\FileScanner;

class FileScannerTest extends TestCase
{
    public function testFileScannerCanReturnClasses()
    {
        $tokenScanner = new FileScanner(__DIR__ . '/../TestAsset/MultipleNamespaces.php');
        $this->assertEquals(
            'ZendTest\Code\TestAsset\Baz',
            $tokenScanner->getClass('ZendTest\Code\TestAsset\Baz')->getName()
        );
        $this->assertEquals('Foo', $tokenScanner->getClass('Foo')->getName());
    }
}
