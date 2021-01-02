<?php

declare(strict_types=1);

namespace Chubbyphp\Laminas\Config\Factory;

use Psr\Container\ContainerInterface;

abstract class AbstractFactory
{
    protected string $name;

    final public function __construct(string $name = '')
    {
        $this->name = $name;
    }

    /**
     * @param array<int, ContainerInterface> $arguments
     *
     * @return mixed
     */
    final public static function __callStatic(string $name, array $arguments)
    {
        return (new static($name))($arguments[0]);
    }

    /**
     * @return mixed
     */
    abstract public function __invoke(ContainerInterface $container);

    protected function resolveDependency(ContainerInterface $container, string $class, string $factoryClass): object
    {
        if ($container->has($class.$this->name)) {
            return $container->get($class.$this->name);
        }

        return (new $factoryClass($this->name))($container);
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    protected function resolveConfig(array $config): array
    {
        if ('' === $this->name) {
            return $config;
        }

        return $config[$this->name] ?? [];
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function resolveValue(ContainerInterface $container, $value)
    {
        if (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                $value[$subKey] = $this->resolveValue($container, $subValue);
            }

            return $value;
        }

        return is_string($value) && $container->has($value) ? $container->get($value) : $value;
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function callSetters(ContainerInterface $container, object $object, array $config): object
    {
        foreach ($config as $key => $value) {
            $object->{'set'.ucfirst($key)}($this->resolveValue($container, $value));
        }

        return $object;
    }
}
