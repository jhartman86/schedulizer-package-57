<?php
/**
 * Tests for Schedulizer package.
 * @important: Obviously running these tests shouldn't ever be done against a live
 * environment, but beware that these tests run against the database you might be
 * developing on. These will WIPE OUT wherever your at.
 * @ref: https://jtreminio.com/2013/03/unit-testing-tutorial-introduction-to-phpunit/
 * @ref: http://code.tutsplus.com/tutorials/all-about-mocking-with-phpunit--net-27252
 * @ref: http://jamesmcfadden.co.uk/phpunit-and-doctrine-2-orm-caching-issues/
 */

use Concrete\Core\Config\Repository;

// Bootstrap the CMS
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)));
define('C5_ENVIRONMENT_ONLY', true);
define('C5_EXECUTE', true);
define('DIR_BASE', realpath(dirname(__FILE__) . '/../../../../web'));
require DIR_BASE . "/concrete/bootstrap/configure.php";
require DIR_BASE . "/concrete/bootstrap/autoload.php";
$cms = require DIR_BASE . "/concrete/bootstrap/start.php";

// Setup autoloading targeted at the _tests directory
$symfonyLoader = new \Concrete\Core\Foundation\ModifiedPSR4ClassLoader();
$symfonyLoader->addPrefix('Schedulizer\\Tests', DIR_PACKAGES . '/schedulizer/_tests/Tests');
$symfonyLoader->register();

// Setup database connection
$config = $cms->make('config');
$config->set('database.default-connection', 'concrete');
$config->set('database.connections.concrete', array(
    'driver'    => 'c5_pdo_mysql',
    'server'    => '127.0.0.1',
    'database'  => 'dev_site',
    'username'  => 'concrete5',
    'password'  => 'concrete5',
    'charset'   => 'utf8'
));
$config->get('concrete');
$config->set('concrete.cache.blocks', false);
$config->set('concrete.cache.pages', false);
$config->set('concrete.cache.enabled', false);

// C5 automatically detects if run through command line interface and stops bootstrapping
// before package initialization. Setup is below (see concrete/bootstrap/start.php)
include DIR_APPLICATION . '/bootstrap/app.php';
$cms->setupPackages();

// By default, lets make sure the package is installed...
$packageObj = Concrete\Core\Package\Package::getByHandle('schedulizer');
if( ! is_object($packageObj) ){
    Concrete\Core\Package\Package::getClass('schedulizer')->install();
}

// Unset so it doesn't fuck w/ PHPUnit too hard
unset($cms);