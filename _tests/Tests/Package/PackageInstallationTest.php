<?php namespace Schedulizer\Tests\Package {

    use Loader;
    use Database;
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

        public function testSpecificPackageUpdates(){
            $pkg = Package::getByHandle('schedulizer');
            $pkg->upgradeCoreData();
            $pkg->upgrade();
        }

        /**
         * find stranded records (tag association references non-existent event): just switch
         * outer select -> "delete" to remove orphans.
         * ---------------------------------------------------------------
         *   select * from SchedulizerTaggedEvents WHERE NOT EXISTS (
         *     select * from SchedulizerEvent where SchedulizerEvent.id = SchedulizerTaggedEvents.eventID
         *   );
         */
//        public function testHey(){
//            $connection          = Database::connection(Database::getDefaultConnection())->getWrappedConnection();
//            $existing            = $connection->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_TYPE = 'FOREIGN KEY'");
//            $existing->execute();
//            // = array of existing foreign key names already configured
//            $existingConstraints = $existing->fetchAll(\PDO::FETCH_COLUMN);
//
//            $constraints = array(
//                'FK_calendar' => array(
//                    'table' => 'SchedulizerEvent', 'fkCol' => 'calendarID', 'fkRefs' => 'SchedulizerCalendar(id)', 'cascades' => array('update', 'delete')
//                ),
//                'FK_event' => array(
//                    'table' => 'SchedulizerEventVersion', 'fkCol' => 'eventID', 'fkRefs' => 'SchedulizerEvent(id)', 'cascades' => array('delete')
//                ),
//                'FK_event2' => array(
//                    'table' => 'SchedulizerEventTime', 'fkCol' => 'eventID', 'fkRefs' => 'SchedulizerEvent(id)', 'cascades' => array('update', 'delete')
//                ),
//                'FK_eventTime' => array(
//                    'table' => 'SchedulizerEventTimeWeekdays', 'fkCol' => 'eventTimeID', 'fkRefs' => 'SchedulizerEventTime(id)', 'cascades' => array('update', 'delete')
//                ),
//                'FK_eventTime2' => array(
//                    'table' => 'SchedulizerEventTimeNullify', 'fkCol' => 'eventTimeID', 'fkRefs' => 'SchedulizerEventTime(id)', 'cascades' => array('update', 'delete')
//                ),
//                'FK_taggedEvent' => array(
//                    'table' => 'SchedulizerTaggedEvents', 'fkCol' => 'eventID', 'fkRefs' => 'SchedulizerEvent(id)', 'cascades' => array('delete')
//                ),
//                'FK_taggedEvent2' => array(
//                    'table' => 'SchedulizerTaggedEvents', 'fkCol' => 'eventTagID', 'fkRefs' => 'SchedulizerEventTag(id)', 'cascades' => array('delete')
//                ),
//                'FK_categorizedEvent' => array(
//                    'table' => 'SchedulizerCategorizedEvents', 'fkCol' => 'eventID', 'fkRefs' => 'SchedulizerEvent(id)', 'cascades' => array('delete')
//                ),
//                'FK_categorizedEvent2' => array(
//                    'table' => 'SchedulizerCategorizedEvents', 'fkCol' => 'eventCategoryID', 'fkRefs' => 'SchedulizerEventCategory(id)', 'cascades' => array('delete')
//                )
//            );
//
//            foreach($constraints AS $constrName => $def){
//                if( !in_array($constrName, $existingConstraints) ){
//                    $query = "ALTER TABLE {$def['table']}
//                    ADD CONSTRAINT {$constrName}
//                    FOREIGN KEY ({$def['fkCol']})
//                    REFERENCES {$def['fkRefs']}";
//                    $query .= in_array('update', $def['cascades']) ? ' ON UPDATE CASCADE' : '';
//                    $query .= in_array('delete', $def['cascades']) ? ' ON DELETE CASCADE' : '';
//                    $connection->exec($query);
//                }
//            }
//
//        }

//        public function testInstall(){
////            if( Package::getByHandle('schedulizer') ){
////                Package::getByHandle('schedulizer')->uninstall();
////            }
////            Package::getClass('schedulizer')->install();
//        }

//        public function testUpgrade(){
//            if( $pkg = Package::getByHandle('schedulizer') ){
//                $pkg->upgrade();
//            }
//        }

    }

}