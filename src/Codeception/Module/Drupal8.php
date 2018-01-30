<?php

namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\TestDrupalKernel;
use Codeception\TestInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\PhpStorage\PhpStorageFactory;
use Symfony\Component\HttpFoundation\Request;

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
                'clear_caches' => false,
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

    /**
     * Create a cleanly booted environemnt for every test.
     *
     * @param TestInterface $test
     */
    public function _before(TestInterface $test)
    {
        $app_root = realpath($this->config['app_root']);
        $class_loader = require $app_root . '/autoload.php';
        $kernel = new TestDrupalKernel($this->config['environment'], $class_loader, false, $app_root);
        $kernel->setSitePath($this->config['site_path']);
        $request = Request::create('/');
        $kernel->prepareLegacyRequest($request);

        // Clean up everything, slow but thorough.
        if ($this->config['clear_caches']) {
            $module_handler = \Drupal::moduleHandler();
            // Flush all persistent caches.
            $module_handler->invokeAll('cache_flush');
            foreach (Cache::getBins() as $cache_backend) {
                $cache_backend->deleteAll();
            }

            // Reset all static caches.
            drupal_static_reset();

            // Wipe the Twig PHP Storage cache.
            PhpStorageFactory::get('twig')->deleteAll();
        }
    }

    /**
     * Setup Test environment.
     *
     * @param array $settings
     */
    public function _beforeSuite($settings = [])
    {
        if ($this->config['create_users']) {
            $this->scaffoldTestUsers();
        }
    }

    /**
     * Tear down after tests.
     */
    public function _afterSuite()
    {
        if ($this->config['destroy_users']) {
            $this->tearDownTestUsers();
        }
    }

}
