<?php

namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Lib\Framework;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
 * Class Drupal8Module
 * @package Codeception\Module
 */
class Drupal8 extends Module
{

    /**
     * Drupal8Module constructor.
     */
    public function __construct(ModuleContainer $container, $config = null)
    {
        $this->config = array_merge(
          [
            'drupal_root' => __DIR__ . '/web',
            'create_users' => true,
            'destroy_users' => true,
            'test_user_pass' => 'password'
          ],
          (array)$config
        );
        parent::__construct($container);
    }

    /**
     * Setup Test environment.
     */
    public function _beforeSuite()
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

    /**
     * Create a test user based on a role.
     *
     * @param string $role
     *
     * @return int
     */
    public function createTestUser($role = 'administrator')
    {
        return User::create([
          'name' => "test{$role}User",
          'mail' => "test{$role}User@example.com",
          'roles' => [$role],
          'pass' => $this->config['test_user_pass'],
          'status' => 1,
        ])->save();
    }

    /**
     * Destroy a user that matches a test user name.
     *
     * @param $role
     */
    public function destroyTestUser($role)
    {
        $users = \Drupal::entityQuery('user')
                        ->condition("name", "test{$role}User")
                        ->execute();

        array_map(user_delete($uid), $users);
    }

    /**
     * Create a test user for each role in Drupal database.
     *
     * @return array
     */
    public function scaffoldTestUsers()
    {
        $roles = Role::loadMultiple();

        return array_map($this->createTestUser($role), $roles);
    }

    /**
     * Remove all users matching test user names.
     *
     * @return array
     */
    public function tearDownTestUsers()
    {
        $roles = Role::loadMultiple();

        return array_map($this->destroyTestUser($role), $roles);
    }
}