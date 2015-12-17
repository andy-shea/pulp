<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Octahedron Pty Ltd <andrew@octahedron.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octahedron\Pulp;

/**
 * A support class for `Module`s to ease implementation by reducing repetition.
 * Mirrors Binder functions for a more readable configuration.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
 */
abstract class AbstractModule implements Module {

  protected $binder;

  public function __construct(Binding\Binder $binder) {
    $this->binder = $binder;
  }

  protected function bind($interface) {
    return $this->binder->bind($interface);
  }

  protected function install(Assisted\FactoryProvider $factoryProvider) {
    $this->binder->installFactoryProvider($factoryProvider);
  }

}
