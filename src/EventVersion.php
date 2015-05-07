<?php namespace Concrete\Package\Schedulizer\Src {

    use DateTime,
        DateTimeZone,
        \Concrete\Package\Schedulizer\Src\EventTime,
        \Concrete\Package\Schedulizer\Src\Persistable\Contracts\Persistant,
        \Concrete\Package\Schedulizer\Src\Persistable\DefinitionInspector,
        \Concrete\Package\Schedulizer\Src\Persistable\Handler;

    /**
     * @package Concrete\Package\Schedulizer\Src
     * @definition({"table":"SchedulizerEventVersion"})
     */
    abstract class EventVersion extends Persistant {

        const USE_CALENDAR_TIMEZONE_TRUE    = true,
              USE_CALENDAR_TIMEZONE_FALSE   = false,
              EVENT_COLOR_DEFAULT           = '#E1E1E1';

        /** @definition({"cast":"int"}) */
        protected $eventID;

        /** @definition({"cast":"int"}) */
        protected $versionID;

        /** @definition({"cast":"string","nullable":true}) */
        protected $title;

        /** @definition({"cast":"string","nullable":true}) */
        protected $description;

        /** @definition({"cast":"bool","nullable":false}) */
        protected $useCalendarTimezone = self::USE_CALENDAR_TIMEZONE_TRUE;

        /** @definition({"cast":"string","nullable":false}) */
        protected $timezoneName = self::DEFAULT_TIMEZONE;

        /** @definition({"cast":"string","nullable":true}) */
        protected $eventColor = self::EVENT_COLOR_DEFAULT;

        /** @definition({"cast":"int","nullable":true}) */
        protected $fileID;

        /**
         * @param $setters
         */
        public function __construct( $setters = null ){
            $this->mergePropertiesFrom($setters);
        }

        /** @return string */
        public function __toString(){ return ucwords( $this->title ); }

        /** @return int|null */
        public function getVersionID(){ return $this->versionID; }

        /** @return string|null */
        public function getTitle(){ return $this->title; }

        /** @return string|null */
        public function getDescription(){ return $this->description; }

        /** @return bool|null */
        public function getUseCalendarTimezone(){ return $this->useCalendarTimezone; }

        /** @return string|null */
        public function getTimezoneName(){ return $this->timezoneName; }

        /** @return string|null */
        public function getEventColor(){ return $this->eventColor; }

        /** @return int|null */
        public function getFileID(){ return $this->fileID; }

        /**
         * Mark the current entity as the approved one
         */
//        public function markVersionApproved(){
//            /** @var $connection \PDO */
//            $connection = \Core::make('SchedulizerDB');
//            $connection->beginTransaction();
//            $statement1 = $connection->prepare("UPDATE SchedulizerEventVersion SET isApproved = 0 WHERE eventID = :eventID");
//            $statement1->bindValue(':eventID', $this->eventID);
//            $statement1->execute();
//            $statement2 = $connection->prepare("UPDATE SchedulizerEventVersion SET isApproved = 1 WHERE eventID = :eventID AND versionID = :versionID");
//            $statement2->bindValue(':eventID', $this->eventID);
//            $statement2->bindValue(':versionID', $this->versionID);
//            $statement2->execute();
//            $connection->commit();
//            $this->isApproved = true;
//        }

        /**
         * Every time an event gets saved, we have to create a new
         * version. This handles that. Note - we're not using the default Handler
         * stuff here, but instead calling the createStatement directly as
         * EventVersions shouldn't be *updated*, they should always create new records.
         */
        protected function save_version(){
            $handler = new Handler(DefinitionInspector::parse(__CLASS__), $this);
            // All this hoopla is so that we can automatically generate the versionID
            // during an insert statement (eg. find whatever the highest version number is
            // for the current event, and create a new record with the version + 1).
            $handler->createStatement(function( $tableName, $columnNames ){
                $columns = join(',', $columnNames);
                $params  = join(',', array_map(function( $col ) use ($tableName){
                    if( $col == 'versionID' ){
                        return "(SELECT COALESCE(MAX(versionID), 0) + 1 FROM {$tableName} WHERE eventID = :eventID)";
                    }
                    return ":{$col}";
                }, $columnNames));
                // @note: customized insert statement doesn't use (COLUMNS) VALUES(...), but
                // instead SELECT, so we can have the version statement above!
                return "INSERT INTO {$tableName} ({$columns}) SELECT {$params}";
            })->execute();

            // Now, update the versionID property on this object by seeing what the
            // last record inserted was (for the EventVersion table) and pull the
            // versionID
            $statement = $handler->connection()->prepare("SELECT versionID
            FROM SchedulizerEventVersion sev WHERE sev.id = :eventVersionID");
            $statement->bindValue(':eventVersionID', $handler->connection()->lastInsertId());
            $statement->execute();
            $this->versionID = (int)$statement->fetchColumn(0);
        }

    }

}