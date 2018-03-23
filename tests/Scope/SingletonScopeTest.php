<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Pulp\Test\Scope;

use Pulp\Scope\SingletonScope;

class SingletonScopeTest extends \PHPUnit_Framework_TestCase {

  public function testGetDependencyReturnsSingleton() {
    $bindingMock = $this->getMockBuilder('Pulp\Binding\Binding')
        ->disableOriginalConstructor()
        ->setMethods(['createDependency'])
        ->getMock();
    $bindingMock->expects($this->once())
       ->method('createDependency')
       ->will($this->returnValue($bindingMock));

    $injectorMock = $this->getMockBuilder('Pulp\Injector')
        ->disableOriginalConstructor()
        ->getMock();

    $scope = new SingletonScope();
    $object = $scope->getDependency($bindingMock, $injectorMock);
    $this->assertSame($object, $scope->getDependency($bindingMock, $injectorMock));
  }

}
