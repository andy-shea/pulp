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
use ReflectionParameter;
use ReflectionMethod;

/**
 * A provider for automatically generated factories.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class FactoryProvider implements Provider {

  protected static $cacheDir;

  protected Injector $injector;
  protected string $factoryInterface;
  protected string $factoryClass;
  protected string $cacheFilePath;
  protected ?object $factoryImpl = null;

  public static function setCacheDir(string $cacheDir): void {
    if (!is_dir($cacheDir) && (false === @mkdir($cacheDir, 0770, true))) {
      throw new AssistedInjectCacheException('Problem creating cache directory: ' . $cacheDir);
    }
    if (!is_writable($cacheDir)) throw new AssistedInjectCacheException('Cache directory not writable: ' . $cacheDir);
    self::$cacheDir = $cacheDir;
  }

  public function __construct(string $factoryInterface) {
    $this->factoryInterface = $factoryInterface;
    $this->factoryClass = str_replace('\\', '_', $factoryInterface) . 'Impl';
    if (!self::$cacheDir) throw new AssistedInjectCacheException('Cache directory not set, call FactoryProvider::setCacheDir first');
    $this->cacheFilePath = self::$cacheDir . '/' . $this->factoryClass . '.php';
  }

  #[Inject]
  public function initialise(Injector $injector): void {
    $this->injector = $injector;
  }

  public function forInterface(): string {
    return $this->factoryInterface;
  }

  public function get(): object {
    if (!$this->factoryImpl) {
      if (!file_exists($this->cacheFilePath)) $this->createFactory();
      require_once $this->cacheFilePath;
      $this->factoryImpl = new $this->factoryClass($this->injector);
    }
    return $this->factoryImpl;
  }

  protected function createFactory(): void {
    try {
      ob_start();
      include 'template.php';
      file_put_contents($this->cacheFilePath, '<?php' . ob_get_contents());
    }
    finally {
      ob_end_clean();
    }
  }

  protected function createFactoryMethods(): array {
    $reflectedInterface = new ReflectionClass($this->factoryInterface);
    return array_map(function(ReflectionMethod $reflectedMethod) {
      $returnsType = Returns::extractReturnsType($reflectedMethod);
      if (!$returnsType) throw new AssistedInjectException('Missing #[Returns(...)] attribute in factory interface');
      return [
        'name' => $reflectedMethod->getName(),
        'returns' => $returnsType,
        'args' => $this->createFactoryMethodParameters($reflectedMethod->getParameters())
      ];
    }, $reflectedInterface->getMethods());
  }

  protected function createFactoryMethodParameters(array $reflectedParameters): array {
    return array_reduce($reflectedParameters, function(array $parameters, ReflectionParameter $reflectedParameter) {
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
