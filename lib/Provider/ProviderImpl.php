<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Octahedron Pty Ltd <andrew@octahedron.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octahedron\Pulp\Provider;

use Octahedron\Pulp\Injector;

/**
 * An implementation of a Provider used by Pulp to automatically inject a
 * provider which will return the specified dependency.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
 */
class ProviderImpl implements Provider {

  private $injector;
  private $classPath;

  public function __construct(Injector $injector, $classPath) {
    $this->injector = $injector;
    $this->classPath = $classPath;
  }

  public function get() {
    return $this->injector->getInstance($this->classPath);
  }

}
