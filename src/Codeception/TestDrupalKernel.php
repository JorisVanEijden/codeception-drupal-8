<?php

namespace Codeception;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;

/**
 * Class TestDrupalKernel
 * @package Codeception
 */
class TestDrupalKernel extends DrupalKernel
{

    /**
     * Boot up drupal.
     *
     * @param string $site_path
     *   The site path.
     *
     * @throws \LogicException
     */
    public function bootTestEnvironment($site_path)
    {
        if (!$this->prepared) {
            static::bootEnvironment($this->root);
            $this->setSitePath($site_path);
            Settings::initialize($this->root, $site_path, $this->classLoader);
            $this->boot();
            $this->loadLegacyIncludes();
            // Load all enabled modules.
            $this->container->get('module_handler')->loadAll();
            // Register stream wrappers.
            $this->container->get('stream_wrapper_manager')->register();
            $this->prepared = true;
        }
    }
}
