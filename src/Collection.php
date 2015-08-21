<?php namespace Concrete\Package\Schedulizer\Src {

    use Permissions;
    use \Concrete\Package\Schedulizer\Src\Persistable\Contracts\Persistant;
    use \Concrete\Package\Schedulizer\Src\Persistable\Mixins\Crud;

    /**
     * Class Collection
     * @package Concrete\Package\Schedulizer\Src
     * @definition({"table":"SchedulizerCollection"})
     */
    class Collection extends Persistant {

        use Crud;

        /** @definition({"cast":"datetime", "declarable":false, "autoSet":["onCreate"]}) */
        protected $createdUTC;

        /** @definition({"cast":"datetime", "declarable":false, "autoSet":["onCreate","onUpdate"]}) */
        protected $modifiedUTC;

        /** @definition({"cast":"string","nullable":true}) */
        protected $title;

        /** @definition({"cast":"int"}) */
        protected $ownerID;

        /** @param $setters */
        public function __construct( $setters = null ){
            $this->mergePropertiesFrom( $setters );
        }

        /** @return string */
        public function __toString(){ return ucwords( $this->title ); }

        /** @return DateTime|null */
        public function getModifiedUTC(){ return $this->modifiedUTC; }

        /** @return DateTime|null */
        public function getCreatedUTC(){ return $this->createdUTC; }

        /** @return string|null */
        public function getTitle(){ return $this->title; }

        /** @return int|null */
        public function getOwnerID(){ return $this->ownerID; }

        /**
         * This assumes one-to-many calendar associations are passed as
         * an array of calendarIDs in $data->collectionCalendars.
         * @param $data
         * @return static
         */
        public static function create( $data ){
            $colObj = new static();
            $colObj->mergePropertiesFrom($data);
            $colObj->save();

            foreach($data->collectionCalendars AS $calID){
                self::adhocQuery(function( \PDO $connection ) use ($colObj, $calID){
                    $statement = $connection->prepare("INSERT INTO SchedulizerCollectionCalendars (collectionID,calendarID) VALUES (:collectionID,:calendarID)");
                    $statement->bindValue(":collectionID", $colObj->getID());
                    $statement->bindValue(":calendarID", $calID);
                    return $statement;
                });
            }

            return $colObj;
        }

        /****************************************************************
         * Fetch Methods
         ***************************************************************/

        public static function fetchAll(){
            return (array) self::fetchMultipleBy(function( \PDO $connection, $tableName ){
                return $connection->prepare("SELECT * FROM {$tableName}");
            });
        }


        public function fetchAllAvailableEvents(){
            $collectionID = $this->id;
            $query = self::adhocQuery(function( \PDO $connection ) use ($collectionID){
                $statement = $connection->prepare("
                    SELECT
                      _events.id AS eventID,
                      _versionInfo.versionID,
                      _events.isActive,
                      _versionInfo.title AS eventTitle,
                      _calendars.title AS calendarTitle,
                      _collectionEvents.approvedVersionID
                    FROM SchedulizerEvent _events
                    LEFT JOIN (
                      SELECT _eventVersions.*
                      FROM SchedulizerEventVersion _eventVersions
                      INNER JOIN ( SELECT eventID, MAX(versionID) AS maxVersionID FROM SchedulizerEventVersion GROUP BY eventID ) _eventVersions2
                      ON _eventVersions.eventID = _eventVersions2.eventID
                      AND _eventVersions.versionID = _eventVersions2.maxVersionID
                    )
                    AS _versionInfo ON _events.id = _versionInfo.eventID
                    JOIN SchedulizerCalendar _calendars ON _calendars.id = _events.calendarID
                    JOIN SchedulizerCollectionCalendars _collectionCalendars ON _collectionCalendars.calendarID = _events.calendarID
                    LEFT JOIN SchedulizerCollectionEvents _collectionEvents
                      ON _collectionEvents.collectionID = _collectionCalendars.collectionID
                      AND _collectionEvents.eventID = _events.id
                    WHERE _collectionCalendars.collectionID = :collectionID
                    ORDER BY _versionInfo.title ASC");
                $statement->bindValue(':collectionID', $collectionID);
                return $statement;
            });
            return $query->fetchAll(\PDO::FETCH_OBJ);
        }


        public static function fetchEventVersionList( $eventID ){
            $query = self::adhocQuery(function( \PDO $connection ) use ($eventID){
                $statement = $connection->prepare("
                    SELECT * FROM SchedulizerEvent _sevent
                    JOIN SchedulizerEventVersion _seversion ON _seversion.eventID = _sevent.id
                    WHERE _sevent.id = :eventID");
                $statement->bindValue(':eventID', $eventID);
                return $statement;
            });
            return $query->fetchAll(\PDO::FETCH_OBJ);
        }


        public static function fetchApprovedEventVersionID( $collectionID, $eventID ){
            $query = self::adhocQuery(function( \PDO $connection ) use ($collectionID, $eventID){
                $statement = $connection->prepare("
                    SELECT * FROM SchedulizerCollectionEvents
                    WHERE collectionID = :collectionID AND eventID = :eventID");
                $statement->bindValue(':collectionID', $collectionID);
                $statement->bindValue(':eventID', $eventID);
                return $statement;
            });
            return $query->fetch(\PDO::FETCH_OBJ);
        }


        public function approveEventVersion( $eventID, $approvedVersionID ){
            $collectionID = $this->id;
            self::adhocQuery(function( \PDO $connection ) use ($collectionID, $eventID, $approvedVersionID){
                $statement = $connection->prepare("INSERT INTO SchedulizerCollectionEvents (collectionID,eventID,approvedVersionID)
                VALUES (:collectionID,:eventID,:approvedVersionID)
                ON DUPLICATE KEY UPDATE approvedVersionID = :approvedVersionID");
                $statement->bindValue(":collectionID", $collectionID);
                $statement->bindValue(":eventID", $eventID);
                $statement->bindValue(":approvedVersionID", $approvedVersionID);
                return $statement;
            });
        }

    }

}