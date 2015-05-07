<?php namespace Concrete\Package\Schedulizer\Src {

    use Events,
        Package,
        \Concrete\Package\Schedulizer\Src\SystemEvents\EventOnSave AS SystemEventOnSave,
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
              EVENT_ON_SAVE     = 'schedulizer.event_save';

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
            // Fire event
            Events::dispatch(self::EVENT_ON_SAVE, new SystemEventOnSave($this));
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
                $root = \Concrete\Core\Page\Page::getByID($packageObj->configGet($packageObj::CONFIG_EVENT_PAGE_PARENT));
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
                $statement = $connection->prepare("SELECT se.*, sev.eventID, sev.versionID,
                sev.title, sev.description, sev.useCalendarTimezone,
                sev.timezoneName, sev.eventColor, sev.fileID
                FROM SchedulizerEvent se LEFT JOIN SchedulizerEventVersion sev
                ON se.id = sev.eventID
                WHERE se.id = :id {$versionSpecificity} ORDER BY sev.versionID DESC LIMIT 1");
                $statement->bindValue(':id', $id);
                if( (int)$versionID > 0 ){
                    $statement->bindValue(':versionID', (int)$versionID);
                }
                return $statement;
            });
        }

        /** @return array Get all associated event times */
        public function getEventTimes(){
            return (array) EventTime::fetchAllByEventID($this->id, $this->versionID);
        }

        /** @return array Get all associated tags */
        public function getEventTags(){
            return (array) EventTag::fetchTagsByEventID($this->id, $this->versionID);
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
        }

        /****************************************************************
         * Fetch Methods
         ***************************************************************/

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

    }

}