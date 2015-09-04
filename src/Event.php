<?php namespace Concrete\Package\Schedulizer\Src {

    use Page,
        PageCache,
        Events,
        Package,
        \Concrete\Package\Schedulizer\Src\SystemEvents\EventOnSave AS SystemEventOnSave,
        \Concrete\Package\Schedulizer\Src\Calendar,
        \Concrete\Package\Schedulizer\Src\EventVersion,
        \Concrete\Package\Schedulizer\Src\Persistable\Mixins\Crud,
        \Concrete\Package\Schedulizer\Src\Attribute\Mixins\AttributableEntity;

    /**
     * Class EventVersion
     * @package Concrete\Package\Schedulizer\Src
     * @definition({"table":"SchedulizerEvent"})
     */
    class Event extends EventVersion {

        use Crud, AttributableEntity;

        // Required for AttributableEntity trait
        const ATTR_KEY_CLASS    = '\Concrete\Package\Schedulizer\Src\Attribute\Key\SchedulizerEventKey',
              ATTR_VALUE_CLASS  = '\Concrete\Package\Schedulizer\Src\Attribute\Value\SchedulizerEventValue',
              EVENT_ON_SAVE     = 'schedulizer.event_save',
              IS_ACTIVE         = true,
              IS_INACTIVE       = false;

        /** @definition({"cast":"datetime", "declarable":false, "autoSet":["onCreate"]}) */
        protected $createdUTC;

        /** @definition({"cast":"datetime", "declarable":false, "autoSet":["onCreate","onUpdate"]}) */
        protected $modifiedUTC;

        /** @definition({"cast":"int"}) */
        protected $calendarID;

        /** @definition({"cast":"int","nullable":false}) */
        protected $ownerID;

        /** @definition({"cast":"int","nullable":true}) */
        protected $pageID;

        /** @definition({"cast":"bool","nullable":false}) */
        protected $isActive = self::IS_ACTIVE;

        /** @return DateTime|null */
        public function getModifiedUTC(){ return $this->modifiedUTC; }

        /** @return DateTime|null */
        public function getCreatedUTC(){ return $this->createdUTC; }

        /** @return int|null */
        public function getCalendarID(){ return $this->calendarID; }

        /** @return int */
        public function getOwnerID(){ return $this->ownerID; }

        /** @return int */
        public function getPageID(){ return $this->pageID; }

        /** @return bool|null */
        public function getIsActive(){ return $this->isActive; }


        /**
         * On after persist is only called after the canonical Event record
         * gets created, never after an EventVersion row gets created.
         */
        protected function onAfterPersist(){
            $this->eventID = $this->id;
            // Persist the version record
            parent::save_version();
            // Configurable setting - create event pages?
            $this->createEventPageIfConfigured();
            // If a collection has this event marked as autoApprovable, update it
            $this->updateAutoApprovableCollectionEventVersion();
            // Fire event
            Events::dispatch(self::EVENT_ON_SAVE, new SystemEventOnSave($this));
        }


        /**
         * Collections can indicate that an event does not require manual approval to show
         * the latest version. So, whenever an event update occurs, this looks for events that
         * are marked as autoApprovable, and indicates that the latest version should be used.
         * @return void
         */
        protected function updateAutoApprovableCollectionEventVersion(){
            $eventID   = $this->getID();
            $versionID = $this->getVersionID();
            self::adhocQuery(function( \PDO $connection ) use ($eventID, $versionID){
                $statement = $connection->prepare("
                    UPDATE SchedulizerCollectionEvents SET approvedVersionID = :approvedVersionID
                    WHERE eventID = :eventID AND autoApprovable = 1;
                ");
                $statement->bindValue(":eventID", $eventID);
                $statement->bindValue(":approvedVersionID", $versionID);
                return $statement;
            });
        }


        /**
         * Schedulizer Configuration settings allow for automatically
         * creating event pages; this takes care of that.
         */
        protected function createEventPageIfConfigured(){
            /** @var $packageObj \Concrete\Package\Schedulizer\Controller */
            $packageObj = Package::getByHandle(self::PACKAGE_HANDLE);
            if( (bool) $packageObj->configGet($packageObj::CONFIG_EVENT_AUTOGENERATE_PAGES) ){
                // If autogen pages IS enabled, and this event already has a page assigned
                // (eg. we're doing an update on an existing event), then bail out
                if( ! empty($this->pageID) ){
                    return;
                }

                /** @var $root \Concrete\Core\Page\Page */
                $root = Page::getByID($packageObj->configGet($packageObj::CONFIG_EVENT_PAGE_PARENT));
                if( $root->isActive() ){
                    $pageType = \Concrete\Core\Page\Type\Type::getByID($packageObj->configGet($packageObj::CONFIG_EVENT_PAGE_TYPE));
                    if( is_object($pageType) ){
                        /** @var $newPageObj \Concrete\Core\Page\Page */
                        $newPageObj = $root->add($pageType, array(
                            'cName' => $this->getTitle(),
                            'pkgID' => $packageObj->getPackageID()
                        ));
                        // We can't use any CRUD (eg. $this->save()) methods
                        // for updating the pageID column as we don't want
                        // to create a new version and create a loop back
                        // to this method
                        $this->pageID = $newPageObj->getCollectionID();
                        $pageID  = $this->pageID;
                        $eventID = $this->id;
                        self::adhocQuery(function(\PDO $connection, $tableName) use($eventID, $pageID){
                            $statement = $connection->prepare("UPDATE {$tableName} SET pageID=:pageID WHERE id=:eventID");
                            $statement->bindValue(':pageID', $pageID);
                            $statement->bindValue(':eventID', $eventID);
                            return $statement;
                        });
                    }
                }
            }
        }


        /**
         * Attempt to bust the page cache if the page is... cached.
         */
        public function bustPageCache(){
            $pageObj = Page::getByID($this->pageID);
            if( is_object($pageObj) ){
                PageCache::getLibrary()->purge($pageObj);
            }
        }


        /**
         * By default, whenever an event gets saved, a new version gets created and kicks off
         * creating all associated records for that version. Since the isActive status
         * property is at the EVENT level (not the event VERSION level), this method lets
         * us set active status without kicking off a full version update.
         * @param bool $toStatus
         */
        public function setActiveStatusWithoutVersioning( $toStatus ){
            $eventID = $this->getID();

            self::adhocQuery(function( \PDO $connection ) use ($eventID, $toStatus){
                $statement = $connection->prepare("
                    UPDATE SchedulizerEvent SET isActive = :isActive WHERE id = :eventID;
                ");
                $statement->bindValue(":isActive", (bool)$toStatus);
                $statement->bindValue(":eventID", $eventID);
                return $statement;
            });
        }


        /**
         * When returning an event, we have to join the SchedulizerEvent
         * with the APPROVED SchedulizerEventVersion
         * @param $id
         * @param $versionID int|null Null indicates "latest"
         * @return $this|void
         */
        public static function getByID( $id, $versionID = null ){
            return static::fetchOneBy(function(\PDO $connection) use ($id, $versionID){
                // Are we getting a specific event version? Append to where clause if so
                $versionSpecificity = ((int)$versionID > 0) ? "AND sev.versionID = :versionID" : '';
                // Prepare query
                $statement = $connection->prepare("
                    SELECT se.*, sev.eventID, sev.versionID,
                    sev.title, sev.description, sev.useCalendarTimezone,
                    sev.timezoneName, sev.eventColor, sev.fileID
                    FROM SchedulizerEvent se LEFT JOIN SchedulizerEventVersion sev
                    ON se.id = sev.eventID
                    WHERE se.id = :id {$versionSpecificity} ORDER BY sev.versionID DESC LIMIT 1
                ");
                $statement->bindValue(':id', $id);
                if( (int)$versionID > 0 ){
                    $statement->bindValue(':versionID', (int)$versionID);
                }
                return $statement;
            });
        }


        /**
         * Get the Calendar object this event is associated with.
         * @return Calendar
         */
        public function getCalendarObj(){
            if( $this->_calendarObj === null ){
                $this->_calendarObj = Calendar::getByID($this->calendarID);
            }
            return $this->_calendarObj;
        }


        /** @return array Get all associated event times */
        public function getEventTimes(){
            return (array) EventTime::fetchAllByEventID($this->id, $this->versionID);
        }


        /** @return array Get all associated tags */
        public function getEventTags(){
            return (array) EventTag::fetchTagsByEventID($this->id, $this->versionID);
        }


        /** @return array Get all associated tags */
        public function getEventCategories(){
            return (array) EventCategory::fetchCategoriesByEventID($this->id, $this->versionID);
        }


        /** @return array|mixed */
        public function jsonSerialize(){
            if( ! $this->isPersisted() ){
                $properties = (object) get_object_vars($this);
                unset($properties->id);
                return $properties;
            }
            $properties                 = (object) get_object_vars($this);
            $properties->_timeEntities  = $this->getEventTimes();
            $properties->_tags          = $this->getEventTags();
            $properties->_categories    = $this->getEventCategories();
            return $properties;
        }


        /**
         * Callback from the Persistable stuff, executed before entity gets
         * removed entirely. We use this to clear out any attribute stuff.
         */
        protected function onBeforeDelete(){
            $id = $this->id;
            // Delete from primary attribute values table
            self::adhocQuery(function(\PDO $connection) use ($id){
                $statement = $connection->prepare("DELETE FROM SchedulizerEventAttributeValues WHERE eventID=:eventID");
                $statement->bindValue(':eventID', $id);
                return $statement;
            });
            // Delete from search indexed table
            self::adhocQuery(function(\PDO $connection) use ($id){
                $statement = $connection->prepare("DELETE FROM SchedulizerEventSearchIndexAttributes WHERE eventID=:eventID");
                $statement->bindValue(':eventID', $id);
                return $statement;
            });
            // Delete associated page, if it was created
            if( (int) $this->pageID >= 1 ){
                $pageObj = \Concrete\Core\Page\Page::getByID($this->pageID);
                if( is_object($pageObj) ){
                    $pageObj->delete();
                }
            }
        }

        /**
         * By default, an event object does not contain any time information... Which
         * seems counter-intuitive. This method looks through all the associated
         * time entities and finds the earliest start time in UTC.
         */
        public function getEarliestStartTime(){
            if( $this->_calcdEarliestStartTime === null ){
                $allStartUTCTimes = array_map(function( $eventTimeObj ){
                    /** @var $eventTimeObj \Concrete\Package\Schedulizer\Src\EventTime */
                    return $eventTimeObj->getStartUTC();
                }, (array) $this->getEventTimes());
                $this->_calcdEarliestStartTime = array_reduce($allStartUTCTimes, function( $carry, $item ){
                    return ($carry < $item) ? $carry : $item;
                }, $allStartUTCTimes[0]);
            }
            return $this->_calcdEarliestStartTime;
        }


        /**
         * @param $title
         * @return array|null [$this, $this]
         */
        public static function fetchAllByTitle( $title ){
            return self::fetchMultipleBy(function( \PDO $connection, $tableName ) use ($title){
                $statement = $connection->prepare("SELECT * FROM {$tableName} WHERE title LIKE :title");
                $statement->bindValue(':title', "%$title%");
                return $statement;
            });
        }


        /**
         * @param $ownerID
         * @return array|null [$this, $this]
         */
        public static function fetchAllByOwnerID( $ownerID ){
            return self::fetchMultipleBy(function( \PDO $connection, $tableName ) use ($ownerID){
                $statement = $connection->prepare("SELECT * FROM {$tableName} WHERE ownerID=:ownerID");
                $statement->bindValue(':ownerID', $ownerID);
                return $statement;
            });
        }


        /**
         * Gets full data for an event; (includes serializing _timeEntity sub-resources).
         * @param $calendarID
         * @return $this|void
         */
        public static function fetchAllByCalendarID( $calendarID ){
            return self::fetchMultipleBy(function( \PDO $connection, $tableName ) use ($calendarID){
                $statement = $connection->prepare("SELECT * FROM {$tableName} WHERE calendarID=:calendarID");
                $statement->bindValue(':calendarID', $calendarID);
                return $statement;
            });
        }


        /**
         * Return a SIMPLE list of the events (ie. just the records) associated with a calendar.
         * This returns straight table results as opposed to the above where it will return a
         * list that gets serialized via jsonSerializable on all the instaniated event objects.
         * @param $calendarID
         * @return $this|void
         */
        public static function fetchSimpleByCalendarID( $calendarID ){
            /** @var $executedStatement \PDOStatement */
            $executedStatement = self::adhocQuery(function( \PDO $connection, $tableName ) use ($calendarID){
                $statement = $connection->prepare("SELECT * FROM {$tableName} WHERE calendarID=:calendarID");
                $statement->bindValue(':calendarID', $calendarID);
                return $statement;
            });
            return $executedStatement->fetchAll(\PDO::FETCH_OBJ);
        }


        /**
         * Get an event by its page ID
         * @param $pageID
         * @param null $versionID
         * @return $this|void
         */
        public static function getByPageID( $pageID, $versionID = null ){
            return static::fetchOneBy(function(\PDO $connection) use ($pageID, $versionID){
                // Are we getting a specific event version? Append to where clause if so
                $versionSpecificity = ((int)$versionID > 0) ? "AND sev.versionID = :versionID" : '';
                // Prepare query
                $statement = $connection->prepare("
                    SELECT se.*, sev.eventID, sev.versionID,
                    sev.title, sev.description, sev.useCalendarTimezone,
                    sev.timezoneName, sev.eventColor, sev.fileID
                    FROM SchedulizerEvent se LEFT JOIN SchedulizerEventVersion sev
                    ON se.id = sev.eventID
                    WHERE se.pageID = :pageID {$versionSpecificity} ORDER BY sev.versionID DESC LIMIT 1
                ");
                $statement->bindValue(':pageID', $pageID);
                if( (int)$versionID > 0 ){
                    $statement->bindValue(':versionID', (int)$versionID);
                }
                return $statement;
            });
        }


        /**
         * Notice this is NOT a static method; not ideal, but since we getters for
         * finding an event by both eventID and pageID, this just makes it easier and uses
         * one more query. Examples:
         * $event = SchedulizerEvent::getByID(int)->getVersionApprovedByCollection()
         * $event = SchedulizerEvent::getByPageID(int)->getVersionApprovedByCollection()
         *
         * ------------------- IMPORTANT -------------------
         * This returns a NEW INSTANCE of the event, or NULL if there is no approved version of
         * the event in the collection.
         * -------------------------------------------------
         *
         * @return \Concrete\Package\Schedulizer\Src\Event | null
         */
        public function getVersionApprovedByCollection( $collectionID ){
            $eventID = $this->getID();
            return static::fetchOneBy(function( \PDO $connection ) use ($eventID, $collectionID){
                $statement = $connection->prepare("
                    SELECT
                      se.*, sev.eventID, sev.versionID,
                      sev.title, sev.description, sev.useCalendarTimezone,
                      sev.timezoneName, sev.eventColor, sev.fileID
                    FROM SchedulizerEvent se JOIN (
                      SELECT _eventVersions.* FROM SchedulizerEventVersion _eventVersions
                      JOIN SchedulizerCollectionEvents _collectionEvents ON _collectionEvents.eventID = _eventVersions.eventID
                      AND _collectionEvents.approvedVersionID = _eventVersions.versionID
                      WHERE _collectionEvents.collectionID = :collectionID
                    ) AS sev ON se.id = sev.eventID
                    WHERE se.id = :eventID
                ");
                $statement->bindValue(":collectionID", $collectionID);
                $statement->bindValue(":eventID", $eventID);
                return $statement;
            });
        }

    }

}