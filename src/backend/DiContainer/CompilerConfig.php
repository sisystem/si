<?php
namespace Si\DiContainer;

class CompilerConfig extends \WoohooLabs\Zen\Config\AbstractCompilerConfig
{
    private $config = null;

    public function __construct()
    {
        require __DIR__ . '/../App/Initializer.php';
        \Si\App\Initializer::initFrameworkCommand(__DIR__ . "/../../../../../../..", "config");     /// application top directory, config subdirectory
            // autoloading + add namespace for application, create app, run Runner
        $this->config = \Si\App\Ctx::config();
    }

    public function getContainerNamespace(): string
    {
        return $this->config['di_container']['container_namespace'];
    }

    public function getContainerClassName(): string
    {
        return $this->config['di_container']['container_classname'];
    }

    public function useConstructorInjection(): bool
    {
        return $this->config['di_container']['use_constructor_injection'];
    }

    public function usePropertyInjection(): bool
    {
        return $this->config['di_container']['use_property_injection'];
    }

    public function getContainerConfigs(): array
    {
        return [
            new ContainerConfig()
        ];
    }
}
