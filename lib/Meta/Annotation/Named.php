<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Meta\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Allows dependency key to be overriden with the specified name.
 * Used to distinguish between variations of the same type of dependency.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 * @Annotation
 */
class Named extends Annotation {}
