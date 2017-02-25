<?php
namespace Si\DiContainer;

class ContainerConfig extends \WoohooLabs\Zen\Config\AbstractContainerConfig
{
    private $container_conf = null;

    public function __construct()
    {
        $dir = \Si\App\Ctx::app_dir() . "/" . \Si\App\Ctx::conf_dir();
        $this->container_conf = require $dir . "/di_container.conf.php";
    }

    protected function getEntryPoints(): array
    {
        return $this->container_conf['entry_points'];
    }

    protected function getDefinitionHints(): array
    {
        return $this->container_conf['definition_hints'];
    }

    protected function getWildcardHints(): array
    {
        return $this->container_conf['wildcard_hints'];
    }
}
