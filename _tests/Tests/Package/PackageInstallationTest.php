<?php namespace Schedulizer\Tests\Package {

    use Loader;
    use Package;

    /**
     * Class PackageInstallationTest
     * @group package
     * @package Schedulizer\Tests\Package
     * @todo:
     * ✓ Package installs OK
     * ✗ Package won't install on versions < 5.7.3.2
     * ✗ Package update doesn't wipe data
     * ✗ Package update adjust schema correctly
     * ✗ Package uninstall deletes tables
     * ✗ Package uninstall wipes proxy classes
     */
    class PackageInstallationTest extends \PHPUnit_Framework_TestCase {

        public function testInstall(){
            if( Package::getByHandle('schedulizer') ){
                Package::getByHandle('schedulizer')->uninstall();
            }
            //Package::getClass('schedulizer')->install();
        }

    }

}