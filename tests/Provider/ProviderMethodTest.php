<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Octahedron Pty Ltd <andrew@octahedron.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Octahedron\Pulp\Test\Provider;

use Octahedron\Pulp\Provider\ProviderMethod;

class ProviderMethodTest extends \PHPUnit_Framework_TestCase {

  public function testGet() {
    $moduleMock = $this->getMockBuilder('Octahedron\Pulp\Module')
        ->disableOriginalConstructor()
        ->setMethods(['configure', 'testProvider'])
        ->getMock();
    $moduleMock->expects($this->once())->method('testProvider');

    $providerMethod = new ProviderMethod($moduleMock, 'testProvider');
    $providerMethod->get();
  }

}
