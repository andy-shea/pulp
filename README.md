# Pulp

**Latest release: [2.0.0](https://github.com/andy-shea/pulp/releases/latest)**<br/>
**Continuous Integration:** [![Build Status](https://travis-ci.org/andy-shea/pulp.svg?branch=master)](https://travis-ci.org/andy-shea/pulp)<br/>
**Requirements:** PHP 8+

Pulp handles the tedious wiring of object graphs for you leaving your code easier to change, test, and reuse. Think of Pulp's `Inject` as the new `new`.

## Getting Started

The easiest way to get up and running with Pulp is via Composer:

```
composer require andy-shea/pulp
```

From here, illustrating Pulp's usage is best served with a simple example. A security service that authenticates and authorises users in an app is a common requirement. This security service could depend on an authentication strategy and access control list to perform the respective tasks:

```php
class SecurityService {

  protected $strategy;
  protected $acl;

  public function __construct(
    AuthenticationStrategy $strategy,
    AccessControlList $acl
  ) {
    $this->strategy = $strategy;
    $this->acl = $acl;
  }

  public authenticateUser($username, $password) {
    return $this->strategy->autheticate($username, $password);
  }

  public authoriseUser(User $user, Resource $resource) {
    return $this->acl->isAllowed($user, $resource);
  }

}
```

We want to create a `SecurityService` by passing in an `AuthenticationStrategy` and `AccessControlList` implementation so it can fulfill it's roles of authentication and authorisation. Pulp's understanding of this object graph can be configured by `Module`s, the building blocks of `Injector`s:

```php
public class SecurityModule extends AbstractModule {

  protected void configure() {
    $this->bind(AuthenticationStrategy::class)->to(BasicAuthStrategy::class);
    $this->bind(AccessControlList::class)->to(AdminAcl::class);
  }

}
```

This tells Pulp to return an instance of `BasicAuthStrategy` whenever a class requires an `AuthenticationStrategy` and similarly an `AdminAcl` will be realised when an `AccessControlList` is depended on.

We also need to let Pulp know which methods it should look to inject dependencies into. Here the `SecurityService`'s constructor requires the two dependencies so we mark it with the `Inject` attribute:

```php
class SecurityService {

  #[Inject]
  public function __construct(
    AuthenticationStrategy $strategy,
    AccessControlList $acl
  ) {
    ...
  }

}
```

Finally, with a module defined, we can build an `Injector` to create our `SecurityService`:

```php
$injectorBuilder = new InjectorBuilder();
$modules = [new SecurityModule()];
$injector = $injectorBuilder->addModules($modules)->build();
$securityService = $injector->getInstance('SecurityService')
```

## Bindings

For the `Injector` to perform its job correctly, it needs to have an understanding of the application's object graph. This can be configured by providing `Binding`s to your `Injector`.

### Linked Bindings

Linked bindings map a type to its implementation. Here, the interface `AuthenticationStrategy` is mapped to the `BasicAuthStrategy` implementation:

```php
$this->bind(AuthenticationStrategy::class)->to(BasicAuthStrategy::class);
```

Now when the `Injector` encounters a dependency on `AuthenticationStrategy`, it will use a `BasicAuthStrategy`. You can link from a type to any of its subtypes, such as an implementing class or an extending class. You can even link the concrete `BasicAuthStrategy` class to a subclass:

```php
$this->bind(BasicAuthStrategy::class)->to(PersistentBasicAuthStrategy::class);
```

### Instance Bindings

Actual instances of a type can be bound directly. This is usually only useful only for objects that don't have dependencies of their own, such as value objects and primitives, or for objects created via other means:

```php
$basicAuthStrategy = new BasicAuthStrategy();
$this->bind(AuthenticationStrategy::class)->toInstance($basicAuthStrategy);
$this->bind('dbConnectionString')->toInstance('pgsql:host=localhost;port=5432;dbname=anotherdb');
```

**Note:** The second example highlights the ability to bind to a parameter name instead of a type. This can be used for resolving primitive dependencies.

### Provider Bindings

When you need to do more work to create an object, use a `Provides` method. The method must be defined within a module, and it must have a `Provides` attribute with a corresponding bound type. The method will be invoked and the returned object injected whenever an instance of that type is needed:

```php
public class SecurityModule extends AbstractModule {

  protected void configure() {
    ...
  }

  #[Provides(Database::class)]
  public function provideDatabase() {
    $db = new PostgresDatabase('pgsql:host=localhost;port=5432;dbname=testdb');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
  }

}
```

When the work to create an object is bit more involved for single method to handle, moving the code to a provider class starts to make more sense. Pulp contributes a `Provider` interface for this class to implement:

```php
interface Provider {

  function get();

}
```

Provider implementations can receive dependencies of their own. Simply provide the `Inject` attribute just like you would with any other class:

```php
public class DatabaseProvider implements Provider {

  protected $dbLog;

  #[Inject]
  public __construct(Log $dbLog) {
    $this->dbLog = $dbLog;
  }

  public function get() {
    $db = new PostgresDatabase('pgsql:host=localhost;port=5432;dbname=testdb');
    $db->setLog($this->dbLog);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
  }

}
```

Finally, we bind to the provider using the `toProvider` method:

```php
public class SecurityModule extends AbstractModule {

  protected void configure() {
    $this->bind(Database::class)->toProvider(DatabaseProvider::class);
  }

}
```

Pulp also offers automatic generation of providers. This is useful for when the creation of a dependency, though not complex, can be expensive and lazy-loading of the dependency is required or if a cyclic dependency chain needs to be broken:

```php
class BillingService {

  protected $paymentProcessorProvider;

  #[Inject]
  public function __construct(#[Provides(CreditCardProcessor::class)] Provider $paymentProcessorProvider) {
    $this->paymentProcessorProvider = $paymentProcessorProvider;
  }

}
```

Here, instead of passing in a `CreditCardProcessor` directly, we inform Pulp via the `Provides` attribute that a provider is required that can return the desired type when necessary. Pulp will automatically create a provider with a `get` method that returns a `CreditCardProcessor` when called.

Factories are a common pattern used to create an object from a family of objects determined at runtime. However, the code to write factories are tediously repetitious and brittle. Pulp alleviates the need to write the implementation code for factories by generating them automatically from a given factory interface. Given two payment types:

```php
class CreditCardPayment implements Payment {

  #[Inject]
  public function __construct(MerchantGateway $gateway) {
    ...
  }

}

class CashPayment implements Payment {
  ...
}
```

And a factory interface:

```php
interface PaymentFactory {

  #[Returns(CreditCardPayment::class)]
  function createCreditCardPayment();

  #[Returns(CashPayment::class)]
  function createCashPayment();

}
```

After creating a `FactoryProvider` with the `PaymentFactory` interface and installing it in a `Module`:

```php
public class PaymentModule extends AbstractModule {

  protected void configure() {
    $this->install(new FactoryProvider('PaymentFactory'));
  }

}
```

Pulp can automatically create and inject a `PaymentFactory` implementation to build the required payments:

```php
class CashDrawer {

  protected $paymentFactory;

  #[Inject]
  public function __construct(PaymentFactory $paymentFactory) {
    $this->paymentFactory = $paymentFactory;
  }

  public function addCreditCardPayment($amount) {
    $payment = $this->paymentFactory->createCreditCardPayment();
    ...
  }

}
```

Each method in the factory interface must be annotated with `Returns` along with the corresponding type that will be created by the method.

Objects returned from the automatically generated factory implementation will themselves have their dependencies injected. In the example above, when `createCreditCardPayment()` is called, the returned `CreditCardPayment` will have a its `MerchantGateway` resolved.

### Annotated Bindings

Occasionally you will come across the need for different variations of the same type. To account for this, Pulp provides `Qualifier`s and the `Named` attribute as methods for annotating types. For example, your application may need to interact with multiple database sources:

```php
class ClientSecurityService {

  #[Inject]
  public function __construct(Database $clientDatabase) {
    ..
  }

}

class AdminSecurityService {

  #[Inject]
  public function __construct(Database $adminDatabase) {
    ..
  }

}
```

In this scenario, there is no way for Pulp to distinguish between the two databases when binding the dependencies. However by annotating them, we can provide distinct binding targets to resolve the dependencies to.
For qualifiers, first create new `Qualifier` attributes representing the type variances:

```php
#[Attribute(Attribute::TARGET_PARAMETER), Qualifier]
final class ClientDatabase {}

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER), Qualifier]
final class AdminDatabase {}
```

Then annotate the required parameters or properties with the new `Qualifier` attributes:

```php
class ClientSecurityService {

  #[Inject]
  public function __construct(#[ClientDatabase] Database $db) {
    ..
  }

}

class AdminSecurityService {

  #[Inject, AdminDatabase] protected Database $db;

}
```

Finally, bind the qualifiers in a module:

```php
public class SecurityModule extends AbstractModule {

  protected void configure() {
    $this->bind(ClientDatabase::class)->toProvider(ClientDatabaseProvider::class);
    $this->bind(AdminDatabase::class)->toProvider(AdminDatabaseProvider::class);
  }

}
```

Alternatively, this can be accomplished utilising `Named` attributes:

```php
class ClientSecurityService {

  #[Inject]
  public function __construct(#[Named('ClientDatabase')] Database $db) {
    ..
  }

}

class AdminSecurityService {

  #[Inject, Named('AdminDatabase')] protected Database $db;

}

public class SecurityModule extends AbstractModule {

  protected void configure() {
    $this->bind('ClientDatabase')->toProvider(ClientDatabaseProvider::class);
    $this->bind('AdminDatabase')->toProvider(AdminDatabaseProvider::class);
  }

}
```

### Implicit Bindings

It's important to note that not all dependencies need an explicit binding. If an object depends on a concrete class and there are no explicit bindings for this class, Pulp will inject an instance of the concrete class automatically. This is called an implicit binding.

An important implicit binding that is sometimes required is that of the `Injector` itself. In framework code, sometimes you don't know the type you need until runtime. In this rare case you should inject the injector. Code that injects the injector does not self-document its dependencies, so this approach should be used sparingly.

## Scopes

By default, Pulp returns a new instance whenever a dependency is realised. If more flexibility is required, Pulp provides `Scope`s to configure this behaviour. In `Module`s, bindings can be further configured with `Scope`s:

```php
$this->bind(AuthenticationStrategy::class)->to(BasicAuthStrategy::class)->in(Scopes::singleton());
```

A binding does not require a target for scope configuration. To specify the scope of a concrete class, you can use an untargeted binding:

```php
$this->bind(RouteMapper::class)->in(Scopes::singleton());
```

Provider methods can also be configured with scopes:

```php
#[Provides(Database::class), Singleton]
public function provideDatabase() {
  ...
}
```

## Injections

The dependency injection pattern separates behaviour from dependency resolution. Rather than looking up dependencies directly or from factories, the pattern recommends that dependencies are passed in. The process of setting dependencies into an object is called injection.

### Property, Constructor, and Method Injections

Pulp injects any properties, methods, or constructor defined on a class that is annotated with `Inject`:

```php
class SecurityService {

  protected $strategy;
  protected $acl;
  protected $log;
  #[Inject] protected EmailService $emailService;

  #[Inject]
  public function __construct(
    AuthenticationStrategy $strategy,
    AccessControlList $acl
  ) {
    $this->strategy = $strategy;
    $this->acl = $acl;
  }

  #[Inject]
  public function setLog(Log $securityLog) {
    $this->log = $securityLog;
  }

  public authenticateUser($username, $password) {
    return $this->strategy->autheticate($username, $password);
  }

  public authoriseUser(User $user, Resource $resource) {
    return $this->acl->isAllowed($user, $resource);
  }

}
```

### Optional Injections

All dependencies in an injected method or constructor must be resolvable or an exception will be thrown. The exceptions to this rule are parameters that have been defined with a default value; in these instances, Pulp will automatically fallback to the supplied default:

```php
class PostgresDatabase {

  #[Inject]
  public function __construct(
    #[Named('adminConnectionString')] $connectionString = 'pgsql:host=localhost;port=5432;dbname=testdb'
  ) {
    ...
  }

}
```

Here there is no binding found for `adminConnectionString` so Pulp will inject the default value `pgsql:host=localhost;port=5432;dbname=testdb` instead.

### Assisted Injections

Occasionally, a dependency will require parameters that can only be provided by the parent object. A typical pattern to solve this problem is to provide a factory that knows how to create the object when the given parameters are passed in. As shown above in the Provider Bindings section, Pulp solves a lot of the boilerplate here by automatically generating these factories for you when given a factory interface describing the contract required to create the objects. The difference here is we now need to specify the parameters that require parent object contribution so Pulp can build the factory method correctly. To expand on the previous example, our payment types require an amount to be passed in to their constructors:

```php
class CreditCardPayment implements Payment {

  #[Inject]
  public function __construct(MerchantGateway $gateway, #[Assisted] $amount) {
    ...
  }

}

class CashPayment implements Payment {

  #[Inject]
  public function __construct(#[Assisted] $amount) {
    ...
  }

}
```

Note the `Assisted` attributes marking the parameters that require manual contribution. The factory interface now needs matching parameters defined in its creation methods:

```php
interface PaymentFactory {

  #[Returns(CreditCardPayment::class)]
  function createCreditCardPayment($amount);

  #[Returns(CashPayment::class)]
  function createCashPayment($amount);

}

public class PaymentModule extends AbstractModule {

  protected void configure() {
    $this->install(new FactoryProvider('PaymentFactory'));
  }

}
```

When creating the `CreditCardPayment`, Pulp will still automatically inject the `MerchantGateway` dependency but will require the amount to be passed in by the caller:

```php
class CashDrawer {

  protected $paymentFactory;

  #[Inject]
  public function __construct(PaymentFactory $paymentFactory) {
    $this->paymentFactory = $paymentFactory;
  }

  public function addCreditCardPayment($amount) {
    $payment = $this->paymentFactory->createCreditCardPayment($amount);
    ...
  }

}
```
