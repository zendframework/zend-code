<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Scanner;

use Zend\Code\Scanner\AggregateDirectoryScanner;
use Zend\Code\Scanner\DirectoryScanner;

class DerivedClassScannerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesClass()
    {
        $ds = new DirectoryScanner();
        $ds->addDirectory(__DIR__ . '/TestAsset');
        $ads = new AggregateDirectoryScanner();
        $ads->addDirectoryScanner($ds);
        $c = $ads->getClass('ZendTest\Code\Scanner\TestAsset\MapperExample\RepositoryB');
        $this->assertEquals('ZendTest\Code\Scanner\TestAsset\MapperExample\RepositoryB', $c->getName());
    }
}
