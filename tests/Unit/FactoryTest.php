<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Laminas\Config\Factory;

use Chubbyphp\Laminas\Config\Factory\AbstractFactory;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Chubbyphp\Laminas\Config\Factory\AbstractFactory
 *
 * @internal
 */
final class FactoryTest extends TestCase
{
    use MockByCallsTrait;

    /**
     * @dataProvider namesProvider
     */
    public function testInvoke(string $name): void
    {
        /** @var ContainerInterface|MockObject $container */
        $container = $this->getMockByCalls(ContainerInterface::class);

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
        /** @var ContainerInterface|MockObject $container */
        $container = $this->getMockByCalls(ContainerInterface::class);

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

        /** @var ContainerInterface|MockObject $container */
        $container = $this->getMockByCalls(ContainerInterface::class, [
            Call::create('has')->with(\stdClass::class.$name)->willReturn(true),
            Call::create('get')->with(\stdClass::class.$name)->willReturn($dependency),
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
        /** @var ContainerInterface|MockObject $container */
        $container = $this->getMockByCalls(ContainerInterface::class, [
            Call::create('has')->with(\stdClass::class.$name)->willReturn(false),
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
        /** @var ContainerInterface|MockObject $container */
        $container = $this->getMockByCalls(ContainerInterface::class);

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
        /** @var ContainerInterface|MockObject $container */
        $container = $this->getMockByCalls(ContainerInterface::class, [
            Call::create('has')->with('value1')->willReturn(false),
            Call::create('has')->with('value31')->willReturn(true),
            Call::create('get')->with('value31')->willReturn('value333'),
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
        /** @var ContainerInterface|MockObject $container */
        $container = $this->getMockByCalls(ContainerInterface::class, [
            Call::create('has')->with($name)->willReturn(false),
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

    public function testCallSettersWithUnknownKey(): void
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessageMatches('/Call to undefined method .*::setName()/');

        /** @var ContainerInterface|MockObject $container */
        $container = $this->getMockByCalls(ContainerInterface::class);

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
}
