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

use Octahedron\Pulp\Module;

/**
 * An implementation of a `Provider` used by Pulp to automatically inject a
 * provider which will return a dependency created by a provider method defined
 * in a `Module`.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
 * @todo this will need to be able to support provider methods with parameters
 */
class ProviderMethod implements Provider {

  protected $module;
  protected $methodName;

  public function __construct(Module $module, $methodName) {
    $this->module = $module;
    $this->methodName = $methodName;
  }

  public function get() {
    return call_user_func_array(array($this->module, $this->methodName), array());
  }

}
