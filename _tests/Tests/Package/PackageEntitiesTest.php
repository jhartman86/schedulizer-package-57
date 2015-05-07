<?php //namespace Schedulizer\Tests\Package {
//
//    use \Concrete\Core\Package\Package;
//
//    /**
//     * Class PackageEntitiesTest
//     * @package Schedulizer\Tests
//     * @todo:
//     * ✓ Concrete5 Package class structureManager detects proxy files
//     * ✓ Doctrine detects and parses Calendar entity metadata
//     * ✓ Doctrine detects and parses Event entity metadata
//     * ✓ Doctrine detects and parses EventRepeat entity metadata
//     * ✓ Doctrine detects and parses EventRepeatNullify entity metadata
//     * ✓ Doctrine generates Proxy classes
//     * ✗ Destroy proxy classes?
//     */
//    class PackageEntitiesTest extends \PHPUnit_Framework_TestCase {
//
//        use \Schedulizer\Tests\EntityManagerTrait;
//
//        const PROXY_PATH_AND_PREFIX         = DIR_CONFIG_SITE . '/doctrine/proxies/__CG__ConcretePackageSchedulizerSrc';
//
//        protected $cNameCalendar            = 'Concrete\Package\Schedulizer\Src\Calendar';
//        protected $cNameEvent               = 'Concrete\Package\Schedulizer\Src\Event';
//        protected $cNameEventRepeat         = 'Concrete\Package\Schedulizer\Src\EventRepeat';
//        protected $cNameEventRepeatNullify  = 'Concrete\Package\Schedulizer\Src\EventRepeatNullify';
//        protected $cNameEventTag            = 'Concrete\Package\Schedulizer\Src\EventTag';
//
//        protected $proxyClassesNukedAtStart = false;
//
//
//        /**
//         * Runs before every test; but lets only destroy Proxy classes on the first run
//         * just to make sure they're gone.
//         * @throws \Exception
//         */
//        public function setUp(){
//            if( $this->proxyClassesNukedAtStart === false ){
//                $this->packageStructManager()->destroyProxyClasses('ConcretePackageSchedulizerSrc');
//                $this->proxyClassesNukedAtStart = true;
//            }
//        }
//
//
//        /**
//         * Concrete5's custom structure manager detects entities in
//         * the package properly.
//         */
//        public function testCorePackageDetectionOfEntities(){
//            $this->assertTrue($this->packageStructManager()->hasEntities());
//        }
//
//        /**
//         * Calendar metadata being parsed by Doctrine?
//         */
//        public function testCalendarClassEntityMetadataDetected(){
//            $this->assertContains(
//                $this->cNameCalendar,
//                array_keys($this->packageMetadatas()),
//                'Doctrine metadata parser failed to parse Calendar'
//            );
//        }
//
//        /**
//         * Calendar metadata is parsed AND correct?
//         */
//        public function testCalendarClassMetadataCorrect(){
//            /** @var $metaDef \Doctrine\ORM\Mapping\ClassMetadata */
//            $metaDef = $this->packageEntityManager()->getClassMetadata($this->cNameCalendar);
//            $columns = $metaDef->getColumnNames();
//            $this->assertContains('id', $columns);
//            $this->assertContains('title', $columns);
//            $this->assertContains('defaultTimezone', $columns);
//            $this->assertContains('ownerID', $columns);
//            $this->assertContains('createdUTC', $columns);
//            $this->assertContains('modifiedUTC', $columns);
//        }
//
//        /**
//         * Event metadata being parsed by Doctrine?
//         */
//        public function testEventClassEntityMetadataDetected(){
//            $this->assertContains(
//                $this->cNameEvent,
//                array_keys($this->packageMetadatas()),
//                'Doctrine metadata parser failed to parse Event'
//            );
//        }
//
//        /**
//         * Event metadata being parsed AND correct?
//         */
//        public function testEventClassMetadataCorrect(){
//            /** @var $metaDef \Doctrine\ORM\Mapping\ClassMetadata */
//            $metaDef = $this->packageEntityManager()->getClassMetadata($this->cNameEvent);
//            $columns = $metaDef->getColumnNames();
//            $this->assertContains('id', $columns);
//            $this->assertContains('calendarID', $columns);
//            $this->assertContains('title', $columns);
//            $this->assertContains('description', $columns);
//            $this->assertContains('startUTC', $columns);
//            $this->assertContains('endUTC', $columns);
//            $this->assertContains('isAllDay', $columns);
//            $this->assertContains('useCalendarTimezone', $columns);
//            $this->assertContains('timezoneName', $columns);
//            $this->assertContains('eventColor', $columns);
//            $this->assertContains('isRepeating', $columns);
//            $this->assertContains('repeatTypeHandle', $columns);
//            $this->assertContains('repeatEvery', $columns);
//            $this->assertContains('repeatIndefinite', $columns);
//            $this->assertContains('repeatEndUTC', $columns);
//            $this->assertContains('repeatMonthlyMethod', $columns);
//            $this->assertContains('ownerID', $columns);
//            $this->assertContains('createdUTC', $columns);
//            $this->assertContains('modifiedUTC', $columns);
//        }
//
//        /**
//         * EventRepeat metadata being parsed by Doctrine?
//         */
//        public function testEventRepeatClassEntityMetadataDetected(){
//            $this->assertContains(
//                $this->cNameEventRepeat,
//                array_keys($this->packageMetadatas()),
//                'Doctrine metadata parser failed to parse EventRepeat'
//            );
//        }
//
//        /**
//         * EventRepeat metadata being parsed AND correct?
//         */
//        public function testEventRepeatClassMetadataCorrect(){
//            /** @var $metaDef \Doctrine\ORM\Mapping\ClassMetadata */
//            $metaDef = $this->packageEntityManager()->getClassMetadata($this->cNameEventRepeat);
//            $columns = $metaDef->getColumnNames();
//            $this->assertContains('id', $columns);
//            $this->assertContains('eventID', $columns);
//            $this->assertContains('repeatWeek', $columns);
//            $this->assertContains('repeatDay', $columns);
//            $this->assertContains('repeatWeekday', $columns);
//        }
//
//        /**
//         * EventRepeatNullify metadata being parsed by Doctrine?
//         */
//        public function testEventRepeatNullifyClassEntityMetadataDetected(){
//            $this->assertContains(
//                $this->cNameEventRepeatNullify,
//                array_keys($this->packageMetadatas()),
//                'Doctrine metadata parser failed to parse EventRepeatNullify'
//            );
//        }
//
//        /**
//         * EventRepeatNullify metadata being parsed AND correct?
//         */
//        public function testEventRepeatNullifyClassMetadataCorrect(){
//            /** @var $metaDef \Doctrine\ORM\Mapping\ClassMetadata */
//            $metaDef = $this->packageEntityManager()->getClassMetadata($this->cNameEventRepeatNullify);
//            $columns = $metaDef->getColumnNames();
//            $this->assertContains('eventID', $columns);
//            $this->assertContains('hideOnDate', $columns);
//        }
//
//        /**
//         * EventTag metadata being parsed by Doctrine?
//         */
//        public function testEventTagClassEntityMetadataDetected(){
//            $this->assertContains(
//                $this->cNameEventTag,
//                array_keys($this->packageMetadatas()),
//                'Doctrine metadata parser failed to parse EventTag'
//            );
//        }
//
//        /**
//         * EventTag metadata being parsed AND correct?
//         */
//        public function testEventTagClassEntityMetadataCorrect(){
//            /** @var $metaDef \Doctrine\ORM\Mapping\ClassMetadata */
//            $metaDef = $this->packageEntityManager()->getClassMetadata($this->cNameEventRepeatNullify);
//            $columns = $metaDef->getColumnNames();
//            $this->assertContains('tagName', $columns);
//        }
//
//        /**
//         * Creates Proxy Class files OK?
//         */
//        public function testCreatingProxyClasses(){
//            $this->packageStructManager()->generateProxyClasses();
//            $this->assertFileExists(sprintf('%sCalendar.php', self::PROXY_PATH_AND_PREFIX));
//            $this->assertFileExists(sprintf('%sEvent.php', self::PROXY_PATH_AND_PREFIX));
//            $this->assertFileExists(sprintf('%sEventRepeat.php', self::PROXY_PATH_AND_PREFIX));
//            $this->assertFileExists(sprintf('%sEventRepeatNullify.php', self::PROXY_PATH_AND_PREFIX));
//        }
//
//    }
//
//}