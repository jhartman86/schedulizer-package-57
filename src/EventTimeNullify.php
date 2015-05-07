<?php namespace Concrete\Package\Schedulizer\Src {

    use \DateTime;
    use Concrete\Package\Schedulizer\Src\Persistable\Contracts\Persistant;
    use Concrete\Package\Schedulizer\Src\Persistable\Mixins\Crud;

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

    }

}