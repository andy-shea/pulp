<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Octahedron Pty Ltd <andrew@octahedron.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octahedron\Pulp\Test;

use Octahedron\Pulp\InjectorBuilder;
use Doctrine\Common\Annotations\AnnotationReader;

class InjectorBuilderTest extends \PHPUnit_Framework_TestCase {

  public function testBuildInstallsModule() {
    $moduleMock = $this->getMockBuilder('Octahedron\Pulp\Module')
        ->setMethods(['setBinder', 'configure'])
        ->getMock();
    $moduleMock->expects($this->once())->method('setBinder');
    $moduleMock->expects($this->once())->method('configure');

    $injectorBuilder = new InjectorBuilder(new AnnotationReader());
    $injectorBuilder->addModules([$moduleMock]);
    $this->assertInstanceOf('Octahedron\Pulp\Injector', $injectorBuilder->build());
  }

}
