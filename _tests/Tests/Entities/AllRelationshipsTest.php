<?php //namespace Schedulizer\Tests\Entities {
//
//    use Concrete\Package\Schedulizer\Src\Calendar;
//
//    /**
//     * Class AllRelationshipsTest
//     * @package Schedulizer\Tests\Entities
//     * @group cascades
//     */
//    class AllRelationshipsTest extends \Schedulizer\Tests\DatabaseTestCase {
//
//        public function testDeleteCalendarCascadesRemovingAllDependencies(){
//            Calendar::getByID(3)->delete();
//            $this->assertEquals(0, $this->getRawConnection()->query("SELECT count(*) FROM SchedulizerEvent WHERE calendarID = 3")->fetchColumn(0));
//            $this->assertEquals(0, $this->getRawConnection()->query("SELECT count(*) FROM SchedulizerEventRepeat WHERE eventID = 3")->fetchColumn(0));
//            $this->assertEquals(0, $this->getRawConnection()->query("SELECT count(*) FROM SchedulizerEventRepeat WHERE eventID = 4")->fetchColumn(0));
//            $this->assertEquals(0, $this->getRawConnection()->query("SELECT count(*) FROM SchedulizerEventRepeatNullify WHERE eventID = 3")->fetchColumn(0));
//            $this->assertEquals(0, $this->getRawConnection()->query("SELECT count(*) FROM SchedulizerEventRepeatNullify WHERE eventID = 4")->fetchColumn(0));
//        }
//
//    }
//
//}