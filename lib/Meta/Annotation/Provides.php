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
 * Designates the type of dependency to be returned from the automatically
 * injected provider.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 * @Annotation
 */
class Provides extends Annotation {}
