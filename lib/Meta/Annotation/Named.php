<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Octahedron Pty Ltd <andrew@octahedron.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octahedron\Pulp\Meta\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Allows dependency key to be overriden with the specified name.
 * Used to distinguish between variations of the same type of dependency.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
 * @Annotation
 */
class Named extends Annotation {}
