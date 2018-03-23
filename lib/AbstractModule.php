<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp;

/**
 * A support class for `Module`s to ease implementation by reducing repetition.
 * Mirrors Binder functions for a more readable configuration.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
abstract class AbstractModule implements Module {

  protected $binder;

  public function setBinder(Binding\Binder $binder) {
    $this->binder = $binder;
  }

  protected function bind($interface) {
    return $this->binder->bind($interface);
  }

  protected function install(Assisted\FactoryProvider $factoryProvider) {
    $this->binder->installFactoryProvider($factoryProvider);
  }

}
