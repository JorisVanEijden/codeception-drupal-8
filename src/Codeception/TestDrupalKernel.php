<?php

namespace Codeception;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;


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
            // DrupalKernel::boot() is not sufficient as it does not invoke preHandle(),
            // which is required to initialize legacy global variables.
            $request = Request::create('/');
            $this->prepareLegacyRequest($request);
        }
    }
}
