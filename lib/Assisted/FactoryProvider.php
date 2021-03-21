<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Assisted;

use Pulp\Provider\Provider;
use Pulp\Injector;
use Pulp\Meta\Attribute\Inject;
use Pulp\Meta\Attribute\Returns;
use ReflectionClass;

/**
 * A provider for automatically generated factories.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class FactoryProvider implements Provider {

  protected static $cacheDir;

  protected $injector;
  protected $factoryInterface;
  protected $factoryClass;
  protected $cacheFilePath;
  protected $factoryImpl;

  public static function setCacheDir($cacheDir) {
    if (!is_dir($cacheDir) && (false === @mkdir($cacheDir, 0770, true))) {
      throw new AssistedInjectCacheException('Problem creating cache directory: ' . $cacheDir);
    }
    if (!is_writable($cacheDir)) throw new AssistedInjectCacheException('Cache directory not writable: ' . $cacheDir);
    self::$cacheDir = $cacheDir;
  }

  public function __construct($factoryInterface) {
    $this->factoryInterface = $factoryInterface;
    $this->factoryClass = str_replace('\\', '_', $factoryInterface) . 'Impl';
    if (!self::$cacheDir) throw new AssistedInjectCacheException('Cache directory not set, call FactoryProvider::setCacheDir first');
    $this->cacheFilePath = self::$cacheDir . '/' . $this->factoryClass . '.php';
  }

  #[Inject]
  public function initialise(Injector $injector) {
    $this->injector = $injector;
  }

  public function forInterface() {
    return $this->factoryInterface;
  }

  public function get() {
    if (!$this->factoryImpl) {
      if (!file_exists($this->cacheFilePath)) $this->createFactory();
      require_once $this->cacheFilePath;
      $this->factoryImpl = new $this->factoryClass($this->injector);
    }
    return $this->factoryImpl;
  }

  protected function createFactory() {
    try {
      ob_start();
      include 'template.php';
      file_put_contents($this->cacheFilePath, '<?php' . ob_get_contents());
    }
    finally {
      ob_end_clean();
    }
  }

  protected function createFactoryMethods() {
    $reflectedInterface = new ReflectionClass($this->factoryInterface);
    return array_map(function($reflectedMethod) {
      $returnsType = Returns::extractReturnsType($reflectedMethod);
      if (!$returnsType) throw new AssistedInjectException('Missing #[Returns(...)] attribute in factory interface');
      return [
        'name' => $reflectedMethod->getName(),
        'returns' => $returnsType,
        'args' => $this->createFactoryMethodParameters($reflectedMethod->getParameters())
      ];
    }, $reflectedInterface->getMethods());
  }

  protected function createFactoryMethodParameters($reflectedParameters) {
    return array_reduce($reflectedParameters, function($parameters, $reflectedParameter) {
      $name = $reflectedParameter->getName();
      $class = $reflectedParameter->getType() ? $reflectedParameter->getType()->getName() . ' ' : '';
      $argument = $class . '$' . $name;
      if ($reflectedParameter->isDefaultValueAvailable()) {
        $argument .= '=' . var_export($reflectedParameter->getDefaultValue(), true);
      }
      $parameters[$name] = $argument;
      return $parameters;
    }, []);
  }

}
