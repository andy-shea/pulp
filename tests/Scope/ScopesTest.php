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
use Pulp\Scope\InstanceScope;
use Pulp\Scope\SingletonScope;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class ScopesMethodTest extends TestCase {

  public function testInstanceScope() {
    $this->assertInstanceOf(InstanceScope::class, Scopes::instance());
  }

  public function testSingletonScope() {
    $this->assertInstanceOf(SingletonScope::class, Scopes::singleton());
  }

  public function testScopeIsSingleton() {
    $scope = Scopes::instance();
    $otherScope = Scopes::instance();
    $this->assertSame($scope, $otherScope);
  }

  public function testInvalidScope() {
    $this->expectException(InvalidArgumentException::class, 'Invalid scope specified');
    Scopes::invalidScope();
  }

}
