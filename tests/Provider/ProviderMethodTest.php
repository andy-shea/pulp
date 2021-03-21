<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Pulp\Test\Provider;

use Pulp\Module;
use Pulp\Provider\ProviderMethod;
use PHPUnit\Framework\TestCase;

class ProviderMethodTest extends TestCase {

  public function testGet() {
    $moduleMock = $this->getMockBuilder(Module::class)
        ->disableOriginalConstructor()
        ->setMethods(['configure', 'testProvider'])
        ->getMock();
    $moduleMock->expects($this->once())->method('testProvider');

    $providerMethod = new ProviderMethod($moduleMock, 'testProvider');
    $providerMethod->get();
  }

}
