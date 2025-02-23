<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Laminas\Config\Factory;

use Chubbyphp\Laminas\Config\Factory\AbstractFactory;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockObjectBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Chubbyphp\Laminas\Config\Factory\AbstractFactory
 *
 * @internal
 */
final class FactoryTest extends TestCase
{
    /**
     * @dataProvider namesProvider
     */
    public function testInvoke(string $name): void
    {
        $builder = new MockObjectBuilder();

        /** @var ContainerInterface $container */
        $container = $builder->create(ContainerInterface::class, []);

        $factory = new class($name) extends AbstractFactory {
            public function __invoke(ContainerInterface $container)
            {
                $object = new \stdClass();
                $object->name = $this->name;

                return $object;
            }
        };

        $service = $factory($container);

        self::assertInstanceOf(\stdClass::class, $service);

        self::assertSame($name, $service->name);
    }

    /**
     * @dataProvider namesProvider
     */
    public function testCallStatic(string $name): void
    {
        $builder = new MockObjectBuilder();

        /** @var ContainerInterface $container */
        $container = $builder->create(ContainerInterface::class, []);

        $factory = new class($name) extends AbstractFactory {
            public function __invoke(ContainerInterface $container)
            {
                $object = new \stdClass();
                $object->name = $this->name;

                return $object;
            }
        };

        $factoryClass = $factory::class;

        $service = [$factoryClass, $name]($container);

        self::assertInstanceOf(\stdClass::class, $service);

        self::assertSame($name, $service->name);
    }

    /**
     * @dataProvider namesProvider
     */
    public function testResolveDependencyWithExistingService(string $name): void
    {
        $dependency = new \stdClass();
        $dependency->name = $name;

        $builder = new MockObjectBuilder();

        /** @var ContainerInterface $container */
        $container = $builder->create(ContainerInterface::class, [
            new WithReturn('has', [\stdClass::class.$name], true),
            new WithReturn('get', [\stdClass::class.$name], $dependency),
        ]);

        $factory = new class($name) extends AbstractFactory {
            public function __invoke(ContainerInterface $container)
            {
                return $this->resolveDependency($container, \stdClass::class, \stdClass::class);
            }
        };

        $service = $factory($container);

        self::assertInstanceOf(\stdClass::class, $service);

        self::assertSame($name, $service->name);
    }

    /**
     * @dataProvider namesProvider
     */
    public function testResolveDependencyWithoutExistingService(string $name): void
    {
        $builder = new MockObjectBuilder();

        /** @var ContainerInterface $container */
        $container = $builder->create(ContainerInterface::class, [
            new WithReturn('has', [\stdClass::class.$name], false),
        ]);

        $factory = new class($name) extends AbstractFactory {
            public function __invoke(ContainerInterface $container)
            {
                $dependencyFactory = new class extends AbstractFactory {
                    public function __invoke(ContainerInterface $container)
                    {
                        $object = new \stdClass();
                        $object->name = $this->name;

                        return $object;
                    }
                };

                $dependencyFactoryClass = $dependencyFactory::class;

                return $this->resolveDependency($container, \stdClass::class, $dependencyFactoryClass);
            }
        };

        $service = $factory($container);

        self::assertInstanceOf(\stdClass::class, $service);

        self::assertSame($name, $service->name);
    }

    /**
     * @dataProvider namesProvider
     */
    public function testResolveConfig(string $name): void
    {
        $builder = new MockObjectBuilder();

        /** @var ContainerInterface $container */
        $container = $builder->create(ContainerInterface::class, []);

        $factory = new class($name) extends AbstractFactory {
            public function __invoke(ContainerInterface $container)
            {
                $object = new \stdClass();

                $config = ['key1' => 'value1', 'key2' => 2, 'key3' => ['key31' => 'value31', 'key32' => 5]];

                $object->config = '' === $this->name ? $this->resolveConfig($config) : $this->resolveConfig([$this->name => $config]);

                return $object;
            }
        };

        $service = $factory($container);

        self::assertInstanceOf(\stdClass::class, $service);

        self::assertSame(['key1' => 'value1', 'key2' => 2, 'key3' => ['key31' => 'value31', 'key32' => 5]], $service->config);
    }

    /**
     * @dataProvider namesProvider
     */
    public function testResolveValue(string $name): void
    {
        $builder = new MockObjectBuilder();

        /** @var ContainerInterface $container */
        $container = $builder->create(ContainerInterface::class, [
            new WithReturn('has', ['value1'], false),
            new WithReturn('has', ['value31'], true),
            new WithReturn('get', ['value31'], 'value333'),
        ]);

        $factory = new class($name) extends AbstractFactory {
            public function __invoke(ContainerInterface $container)
            {
                $object = new \stdClass();
                $object->value = $this->resolveValue(
                    $container,
                    ['key1' => 'value1', 'key2' => 2, 'key3' => ['key31' => 'value31', 'key32' => 5]]
                );

                return $object;
            }
        };

        $service = $factory($container);

        self::assertInstanceOf(\stdClass::class, $service);

        self::assertSame(
            ['key1' => 'value1', 'key2' => 2, 'key3' => ['key31' => 'value333', 'key32' => 5]],
            $service->value
        );
    }

    /**
     * @dataProvider namesProvider
     */
    public function testCallSetters(string $name): void
    {
        $builder = new MockObjectBuilder();

        /** @var ContainerInterface $container */
        $container = $builder->create(ContainerInterface::class, [
            new WithReturn('has', [$name], false),
        ]);

        $factory = new class($name) extends AbstractFactory {
            public function __invoke(ContainerInterface $container)
            {
                return $this->callSetters(
                    $container,
                    new class {
                        private $name;

                        public function setName(string $name): void
                        {
                            $this->name = $name;
                        }

                        public function getName(): string
                        {
                            return $this->name;
                        }
                    },
                    ['name' => $this->name]
                );
            }
        };

        $service = $factory($container);

        self::assertSame($name, $service->getName());
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function namesProvider(): iterable
    {
        return [
            ['name' => ''],
            ['name' => uniqid('name-')],
            ['name' => uniqid('name-')],
        ];
    }

    public function testCallSettersWithUnknownKey(): void
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessageMatches('/Call to undefined method .*::setName()/');

        $builder = new MockObjectBuilder();

        /** @var ContainerInterface $container */
        $container = $builder->create(ContainerInterface::class, []);

        $factory = new class extends AbstractFactory {
            public function __invoke(ContainerInterface $container)
            {
                return $this->callSetters(
                    $container,
                    new class {},
                    ['name' => $this->name]
                );
            }
        };

        $factory($container);
    }
}
