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
 * Designates the type of dependency to be returned from the automatically
 * injected provider.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
 * @Annotation
 */
class Provides extends Annotation {}
