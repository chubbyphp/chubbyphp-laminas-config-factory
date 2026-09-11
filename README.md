# chubbyphp-laminas-config-factory

[![CI](https://github.com/chubbyphp/chubbyphp-laminas-config-factory/actions/workflows/ci.yml/badge.svg)](https://github.com/chubbyphp/chubbyphp-laminas-config-factory/actions/workflows/ci.yml)
[![Coverage Status](https://coveralls.io/repos/github/chubbyphp/chubbyphp-laminas-config-factory/badge.svg?branch=master)](https://coveralls.io/github/chubbyphp/chubbyphp-laminas-config-factory?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fchubbyphp%2Fchubbyphp-laminas-config-factory%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/chubbyphp/chubbyphp-laminas-config-factory/master)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-laminas-config-factory/v)](https://packagist.org/packages/chubbyphp/chubbyphp-laminas-config-factory)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-laminas-config-factory/downloads)](https://packagist.org/packages/chubbyphp/chubbyphp-laminas-config-factory)
[![Monthly Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-laminas-config-factory/d/monthly)](https://packagist.org/packages/chubbyphp/chubbyphp-laminas-config-factory)

[![bugs](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-laminas-config-factory&metric=bugs)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-laminas-config-factory)
[![code_smells](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-laminas-config-factory&metric=code_smells)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-laminas-config-factory)
[![coverage](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-laminas-config-factory&metric=coverage)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-laminas-config-factory)
[![duplicated_lines_density](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-laminas-config-factory&metric=duplicated_lines_density)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-laminas-config-factory)
[![ncloc](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-laminas-config-factory&metric=ncloc)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-laminas-config-factory)
[![sqale_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-laminas-config-factory&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-laminas-config-factory)
[![alert_status](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-laminas-config-factory&metric=alert_status)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-laminas-config-factory)
[![reliability_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-laminas-config-factory&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-laminas-config-factory)
[![security_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-laminas-config-factory&metric=security_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-laminas-config-factory)
[![sqale_index](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-laminas-config-factory&metric=sqale_index)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-laminas-config-factory)
[![vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-laminas-config-factory&metric=vulnerabilities)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-laminas-config-factory)

## Description

An abstract service factory for [laminas/laminas-servicemanager][2] and any other [PSR-11][10] container that can
be configured with plain PHP arrays, such as [chubbyphp/chubbyphp-container][3] via [chubbyphp/chubbyphp-laminas-config][4],
Aura.Di, Pimple, Auryn, Symfony or PHP-DI.

It solves one recurring problem: a service factory should work both as a single default service and as one of several
named instances (for example `default` and `secondary` database connections) without duplicating the factory code.
The factory carries an optional name, and the helper methods use it to select the matching config section and
dependencies.

The concept originates from [@DASPRiD][5] in [dasprid/container-interop-doctrine][6], now maintained as
[roave/psr-container-doctrine][7]. This package extracts the idea, with small adjustments, so it can serve as the basis
for any service factory.

## Requirements

 * php: ^8.3
 * [psr/container][10]: ^2.0.2

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-laminas-config-factory][1].

```sh
composer require chubbyphp/chubbyphp-laminas-config-factory "^1.5"
```

## Usage

### Writing a factory

Extend `AbstractFactory` and implement `__invoke()`. The base class provides four helpers:

 * `resolveConfig(array $config)`: returns `$config` as is for an unnamed factory, or `$config[$name]` (default `[]`)
   for a named one.
 * `resolveDependency(ContainerInterface $container, string $class, string $factoryClass)`: returns the service
   `$class . $name` from the container if it exists, otherwise creates it with `new $factoryClass($name)`.
   The name is propagated, so a `secondary` factory resolves `secondary` dependencies.
 * `resolveValue(ContainerInterface $container, mixed $value)`: replaces a string with the container service of that
   id if one exists, recursing into arrays. Other values are returned unchanged.
 * `callSetters(ContainerInterface $container, object $object, array $config)`: calls `set<Key>()` for every config
   entry, passing the value through `resolveValue()`.

```php
<?php

declare(strict_types=1);

namespace MyProject\Factory;

use Chubbyphp\Laminas\Config\Factory\AbstractFactory;
use MyProject\Service\ServiceA;
use MyProject\Service\ServiceB;
use MyProject\Service\ServiceC;
use Psr\Container\ContainerInterface;

final class ServiceAFactory extends AbstractFactory
{
    public function __invoke(ContainerInterface $container): ServiceA
    {
        $config = $this->resolveConfig(
            $container->get('config')['serviceA'] ?? []
        );

        $serviceA = new ServiceA(
            $this->resolveDependency(
                $container,
                ServiceB::class,
                ServiceBFactory::class
            ),
            $this->resolveDependency(
                $container,
                ServiceC::class,
                ServiceCFactory::class
            )
        );

        // calls $serviceA->setLogger($container->get('logger'))
        // and $serviceA->setTimeout(30)
        return $this->callSetters($container, $serviceA, $config);
    }
}
```

### Unnamed and named factories

```php
<?php

declare(strict_types=1);

use MyProject\Factory\ServiceAFactory;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$container = ...;

// unnamed: uses config['serviceA']
// and the dependencies ServiceB::class, ServiceC::class
$serviceA = (new ServiceAFactory())($container);

// named: uses config['serviceA']['default']
// and the dependencies ServiceB::class.'default', ServiceC::class.'default'
$serviceA = (new ServiceAFactory('default'))($container);

// named via static call, useful for container definitions
$serviceA = [ServiceAFactory::class, 'default']($container);
```

The static form works because `AbstractFactory::__callStatic()` treats the method name as the factory name.

### Container definition

The static form lets you register named services without writing a class per name. Example for
[laminas/laminas-servicemanager][2]:

```php
<?php

declare(strict_types=1);

use MyProject\Factory\ServiceAFactory;
use MyProject\Service\ServiceA;

return [
    'serviceA' => [
        'default' => ['logger' => 'logger', 'timeout' => 30],
        'secondary' => ['logger' => 'secondaryLogger', 'timeout' => 60],
    ],
    'dependencies' => [
        'factories' => [
            ServiceA::class.'default'
                => [ServiceAFactory::class, 'default'],
            ServiceA::class.'secondary'
                => [ServiceAFactory::class, 'secondary'],
        ],
    ],
];
```

## Copyright

2026 Dominik Zogg

[1]: https://packagist.org/packages/chubbyphp/chubbyphp-laminas-config-factory

[2]: https://packagist.org/packages/laminas/laminas-servicemanager
[3]: https://packagist.org/packages/chubbyphp/chubbyphp-container
[4]: https://packagist.org/packages/chubbyphp/chubbyphp-laminas-config
[5]: https://github.com/DASPRiD
[6]: https://packagist.org/packages/dasprid/container-interop-doctrine
[7]: https://packagist.org/packages/roave/psr-container-doctrine

[10]: https://packagist.org/packages/psr/container
