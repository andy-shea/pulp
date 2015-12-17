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
 * Supplies `Binding` definitions to the `Injector` to build object graphs.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
 */
interface Module {

  function configure();

}
