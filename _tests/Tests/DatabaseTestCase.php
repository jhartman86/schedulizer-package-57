<?php namespace Schedulizer\Tests {

    /**
     * Class DatabaseTestCase
     * @package Schedulizer\Tests
     */
    abstract class DatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase {

        use DatabaseConnectionTrait, PackageTrait;

        /**
         * PHPUnit fixture setup.
         */
        public static function setUpBeforeClass(){
            // Destroy and recreate entire schema?
        }

        /**
         * PHPUnit fixture teardown.
         */
        public static function tearDownAfterClass(){
            //self::setUpBeforeClass();
        }

        /**
         * Use Doctrine's destroy/create schema facilities to destroy and
         * create for each test that extends this DatabaseTestCase class.
         * To override in the implementing class, just remember to parent::setUp()
         */
        public function setUp(){
            $this->execWithoutConstraints(function(){
                parent::setUp();
            });
        }

        /**
         * Turn off MYSQL foreign key constraints
         * @return void
         */
        protected function disableForeignKeyConstraints(){
            $this->getRawConnection()->query('SET foreign_key_checks = 0');
        }

        /**
         * Re-enable foreign key constraints
         * @return void
         */
        protected function enableForeignKeyConstraints(){
            $this->getRawConnection()->query('SET foreign_key_checks = 1');
        }

        /**
         * Pass in a callback to be executed while foreign key constraints are removed,
         * and once finished they'll be re-enabled.
         * @param callable $closure
         */
        public function execWithoutConstraints( \Closure $closure ){
            $this->disableForeignKeyConstraints();
            $closure();
            $this->enableForeignKeyConstraints();
        }

        /**
         * Method is required for extending DatabaseTestCase; this will automatically
         * find out the filename of the test and load a fixture with the same filename
         * in the /fixtures subdirectory.
         * @param null $override
         * @return mixed
         */
        public function getDataSet( $override = null ){
            $reflector   = new \ReflectionClass(get_called_class());
            $fixturePath = dirname($reflector->getFileName()) . DIRECTORY_SEPARATOR . 'fixtures';
            $fileName    = (is_string($override)) ? sprintf('%s.xml', $override) : sprintf('%s.xml', $reflector->getShortName());
            return $this->createXMLDataSet($fixturePath . DIRECTORY_SEPARATOR . $fileName);
        }

    }

}