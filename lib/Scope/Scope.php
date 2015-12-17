<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Octahedron Pty Ltd <andrew@octahedron.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octahedron\Pulp\Scope;

use Octahedron\Pulp\Binding\Binding;
use Octahedron\Pulp\Injector;

/**
 * An interface for a scope which defines the life of a dependency.  Custom
 * defined scopes will need to implement this interface.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
 */
interface Scope {

  function getDependency(Binding $binding, Injector $injector, $assistedParams = null, $isOptional = false);

}
