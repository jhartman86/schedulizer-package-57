<?php namespace Concrete\Package\Schedulizer\VersionUpdates {

    use Concrete\Core\Package\Package;
    use \Concrete\Package\Schedulizer\Src\Calendar;

    /**
     * This update ensures that all calendars have Admins and Calendar Owners
     * set to edit/delete events.
     */
    class V112 {

        public function run(){
            $packageObj = Package::getByHandle('schedulizer');

            // First, set "enable master" on package config
            $packageObj->configSet($packageObj::CONFIG_ENABLE_MULTI_COLLECTIONS, 0);

            // Then create it
            $collectionObj = \Concrete\Package\Schedulizer\Src\Collection::create((object) array(
                'title'     => 'Approvals',
                'ownerID'   => 1, // @todo: might fail on auto-incr systems other than +1
                'collectionCalendars' => array()
            ));

            // Set the master collectionID in the package config
            $packageObj->configSet($packageObj::CONFIG_MASTER_COLLECTION_ID, $collectionObj->getID());

            // Since we're updating *after* lots of things have been created, add all calendars
            $allCalendars = Calendar::fetchAll();
            foreach($allCalendars AS $calendarObj){
                $collectionObj->addOneCalendar($calendarObj);
            }
        }

    }

}
