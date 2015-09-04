<?php namespace Concrete\Package\Schedulizer\Src {

    use \Concrete\Package\Schedulizer\Src\Event;
    use \DateTime;
    use \Concrete\Package\Schedulizer\Src\Persistable\Contracts\Persistant;
    use \Concrete\Package\Schedulizer\Src\Persistable\Mixins\Crud;

    /**
     * @package Concrete\Package\Schedulizer\Src
     * @definition({"table":"SchedulizerEventTimeNullify"})
     */
    class EventTimeNullify extends Persistant {

        use Crud;

        /** @definition({"cast":"int"}) */
        protected $eventTimeID;

        /** @definition({"cast":"datetime"}) */
        protected $hideOnDate;

        public function jsonSerialize(){
            $properties = (object) get_object_vars($this);
            if( $this->hideOnDate instanceof DateTime ){
                $properties->hideOnDate = $this->hideOnDate->format('c');
            }
            return $properties;
        }

        /****************************************************************
         * Fetch Methods
         ***************************************************************/

        public static function fetchAllByEventTimeID( $eventTimeID ){
            return self::fetchMultipleBy(function( \PDO $connection, $tableName ) use ($eventTimeID){
                $statement = $connection->prepare("SELECT * FROM {$tableName} WHERE eventTimeID=:eventTimeID ORDER BY hideOnDate asc");
                $statement->bindValue(':eventTimeID', $eventTimeID);
                return $statement;
            });
        }


        /**
         * The only scenario this should get used is for permissioning: ie. the user is
         * deleting an eventTimeNullifier, and needs to check the user has permission
         * to do something via the API.
         * @return \Concrete\Package\Schedulizer\Src\Event
         */
        public function getEventObject(){
            $nullifierID = $this->getID();

            $prepared = self::adhocQuery(function( \PDO $connection ) use ($nullifierID){
                $statement = $connection->prepare("
                    SELECT sev.id FROM SchedulizerEvent sev
                    JOIN SchedulizerEventTime sevTime ON sevTime.eventID = sev.id
                    JOIN SchedulizerEventTimeNullify sevTimeNullify ON sevTimeNullify.eventTimeID = sevTime.id
                    WHERE sevTimeNullify.id = :nullifierID
                ");
                $statement->bindValue(":nullifierID", $nullifierID);
                return $statement;
            });

            return Event::getByID($prepared->fetch(\PDO::FETCH_COLUMN));
        }

    }

}