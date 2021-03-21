<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Test;

use Pulp\Injector;
use Pulp\InjectorBuilder;
use Pulp\Module;
use PHPUnit\Framework\TestCase;

class InjectorBuilderTest extends TestCase {

  public function testBuildInstallsModule() {
    $moduleMock = $this->getMockBuilder(Module::class)
        ->setMethods(['setBinder', 'configure'])
        ->getMock();
    $moduleMock->expects($this->once())->method('setBinder');
    $moduleMock->expects($this->once())->method('configure');

    $injectorBuilder = new InjectorBuilder();
    $injectorBuilder->addModules([$moduleMock]);
    $this->assertInstanceOf(Injector::class, $injectorBuilder->build());
  }

}
