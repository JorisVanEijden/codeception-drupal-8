<?php

namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\TestDrupalKernel;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class Drupal8Module
 * @package Codeception\Module
 */
class Drupal8 extends Module
{
    /**
     * Drupal8Module constructor.
     *
     * @param ModuleContainer $container
     * @param null $config
     */
    public function __construct(ModuleContainer $container, $config = null)
    {
        $new_config = array_merge(
            [
                'environment' => 'test',
                'app_root' => Configuration::projectDir() . 'web',
                'site_path' => 'sites/default',
            ],
            (array)$config
        );

        parent::__construct($container, $new_config);
    }

    public function _initialize()
    {
        $site_path = $this->config['site_path'];
        $app_root = realpath($this->config['app_root']);
        $environment = $this->config['environment'];

        // Bootstrap a bare minimum Kernel so we can interact with Drupal.
        $class_loader = require $app_root . '/autoload.php';
        $kernel = new TestDrupalKernel($environment, $class_loader, true, $app_root);
        // Drupal still doesn't work quite right when you don't.
        chdir($app_root);
        $kernel->bootTestEnvironment($site_path);
    }

}
