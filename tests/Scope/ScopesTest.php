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

use Pulp\Scope\Scopes;

class ScopesMethodTest extends \PHPUnit_Framework_TestCase {

  public function testInstanceScope() {
    $this->assertInstanceOf('Pulp\Scope\InstanceScope', Scopes::instance());
  }

  public function testSingletonScope() {
    $this->assertInstanceOf('Pulp\Scope\SingletonScope', Scopes::singleton());
  }

  public function testScopeIsSingleton() {
    $scope = Scopes::instance();
    $otherScope = Scopes::instance();
    $this->assertSame($scope, $otherScope);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Invalid scope specified
   */
  public function testInvalidScope() {
    Scopes::invalidScope();
  }

}
