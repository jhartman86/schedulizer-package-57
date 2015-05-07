<?php //namespace Schedulizer\Tests\Package {
//
//    use Database;
//    use Concrete\Core\Cache\Adapter\DoctrineCacheDriver;
//    use Concrete\Core\Package\Package;
//    use \Doctrine\ORM\Tools\SchemaTool;
//    use Illuminate\Support\Facades\Schema;
//
//    /**
//     * This isn't for testing whether Concrete5's mechanism for installing the
//     * package actually works, but for whether it was installed CORRECTLY. (Whether
//     * it actually installs at all is outside of the scope of these tests).
//     *
//     * Class PackageInstallationTest
//     * @package Schedulizer\Tests\Package
//     */
//    class PackageSchemaTest extends \PHPUnit_Framework_TestCase {
//
//        public function setUp(){
//            $this->packageObj    = Package::getByHandle('schedulizer');
//            $this->structManager = $this->packageObj->getDatabaseStructureManager();
//            $this->entityManager = $this->structManager->getEntityManager();
//        }
//
//        /**
//         * Drop the specific tables.
//         */
//        public function testDestroySchema(){
//            $this->entityManager->clear();
//            $tool       = new SchemaTool($this->entityManager);
//            $entities   = $this->structManager->getMetadatas();
//            $tool->dropSchema($entities);
//        }
//
//        /**
//         * @depends testDestroySchema
//         */
//        public function testCreateSchema(){
//            $this->entityManager->clear();
//            $tool       = new SchemaTool($this->entityManager);
//            $entities   = $this->structManager->getMetadatas();
//            $tool->createSchema($entities);
//        }
//
//        public function testInspectCalendarSchema(){
//            $this->entityManager->clear();
//            $tool       = new SchemaTool($this->entityManager);
//            $entities   = $this->structManager->getMetadatas();
//            $schema     = $tool->getSchemaFromMetadata($entities);
//        }
//
//        public function testPackageUninstall(){
//            $packageObj = Package::getByHandle('schedulizer');
//            if( is_object($packageObj) ){
//                $packageObj->uninstall();
//            }
//            $this->assertNull(Package::getByHandle('schedulizer'));
//        }
//
//        public function testPackageInstall(){
//            Package::getClass('schedulizer')->install();
//            $this->assertInstanceOf('Concrete\Core\Package\Package', Package::getByHandle('schedulizer'));
//        }
//
//    }
//
//}