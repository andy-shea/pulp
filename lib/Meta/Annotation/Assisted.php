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
 * Designates a parameter that will not be injected.
 * Used by Pulp in the creation of `FactoryProvider`s.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 * @Annotation
 */
class Assisted extends Annotation {}
