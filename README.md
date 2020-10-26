# chubbyphp-laminas-config-factory

[![Build Status](https://api.travis-ci.org/chubbyphp/chubbyphp-laminas-config-factory.png?branch=master)](https://travis-ci.org/chubbyphp/chubbyphp-laminas-config-factory)
[![Coverage Status](https://coveralls.io/repos/github/chubbyphp/chubbyphp-laminas-config-factory/badge.svg?branch=master)](https://coveralls.io/github/chubbyphp/chubbyphp-laminas-config-factory?branch=master)
[![Infection MSI](https://badge.stryker-mutator.io/github.com/chubbyphp/chubbyphp-laminas-config-factory/master)](https://travis-ci.org/chubbyphp/chubbyphp-laminas-config-factory)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-laminas-config-factory/v/stable.png)](https://packagist.org/packages/chubbyphp/chubbyphp-laminas-config-factory)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-laminas-config-factory/downloads.png)](https://packagist.org/packages/chubbyphp/chubbyphp-laminas-config-factory)
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

An abstract service factory for the [laminas/laminas-servicemanager][2] and any other dependency injection container
who's been able to handle it's config, like [chubbyphp/chubbyphp-container][3] via [chubbyphp/chubbyphp-laminas-config][4]
and many (Aura.Di, Pimple, Auryn, Symfony, PHP-DI) more.

The original concept of this abstract service factory is by [@DASPRiD][5] used in [dasprid/container-interop-doctrine][6]
which was handed over to [roave/psr-container-doctrine][7].

Small adjustments and the possibility to use the concept as a basis for all service factories led me to make it
available as an independent repository.

## Requirements

 * php: ^7.2|^8.0
 * [psr/container][10]: ^1.0

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-laminas-config-factory][1].

```sh
composer require chubbyphp/chubbyphp-laminas-config-factory "^1.0"
```

## Usage

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
        return new ServiceA(
            $this->resolveConfig($container->get('config')['serviceA'] ?? []),
            $this->resolveDependency($container, ServiceB::class, ServiceBFactory::class),
            $this->resolveDependency($container, ServiceC::class, ServiceCFactory::class)
        );
    }
}

/** @var ContainerInterface $container */
$container = ...;

// without name
$serviceA = (new ServiceAFactory())($container);

// with name
$serviceA = [ServiceAFactory::class, 'default']($container);
```

## Copyright

Dominik Zogg 2020

[1]: https://packagist.org/packages/chubbyphp/chubbyphp-laminas-config-factory

[2]: https://packagist.org/packages/laminas/laminas-servicemanager
[3]: https://packagist.org/packages/chubbyphp/chubbyphp-container
[4]: https://packagist.org/packages/chubbyphp/chubbyphp-laminas-config
[5]: https://github.com/DASPRiD
[6]: https://packagist.org/packages/dasprid/container-interop-doctrine
[7]: https://packagist.org/packages/roave/psr-container-doctrine

[10]: https://packagist.org/packages/psr/container
