<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Provider;

use Pulp\Injector;

/**
 * An implementation of a Provider used by Pulp to automatically inject a
 * provider which will return the specified dependency.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class ProviderImpl implements Provider {

  private Injector $injector;
  private string $classPath;

  public function __construct(Injector $injector, string $classPath) {
    $this->injector = $injector;
    $this->classPath = $classPath;
  }

  public function get(): mixed {
    return $this->injector->getInstance($this->classPath);
  }

}
