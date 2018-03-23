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

use Pulp\InjectorBuilder;
use Doctrine\Common\Annotations\AnnotationReader;

class InjectorBuilderTest extends \PHPUnit_Framework_TestCase {

  public function testBuildInstallsModule() {
    $moduleMock = $this->getMockBuilder('Pulp\Module')
        ->setMethods(['setBinder', 'configure'])
        ->getMock();
    $moduleMock->expects($this->once())->method('setBinder');
    $moduleMock->expects($this->once())->method('configure');

    $injectorBuilder = new InjectorBuilder(new AnnotationReader());
    $injectorBuilder->addModules([$moduleMock]);
    $this->assertInstanceOf('Pulp\Injector', $injectorBuilder->build());
  }

}
