<?php namespace Concrete\Package\Schedulizer\Src {

    use Loader;
    use \Concrete\Package\Schedulizer\Src\Event;
    use \Concrete\Package\Schedulizer\Src\Persistable\Contracts\Persistant;
    use \Concrete\Package\Schedulizer\Src\Persistable\Mixins\Crud;

    /**
     * Class EventCategory
     * @package Concrete\Package\Schedulizer\Src
     * @definition({"table":"SchedulizerEventCategory"})
     */
    class EventCategory extends Persistant {

        use Crud;

        /** @definition({"cast":"string","nullable":false}) */
        protected $displayText;

        /** @definition({"cast":"string","nullable":false}) */
        protected $handle;

        /** @param null $string */
        public function __construct( $string = null ){
            if( $string !== null ){
                $this->displayText = $string;
            }
        }

        /** @return string|null */
        public function __toString(){ return $this->displayText;}

        /** @return string|null */
        public function getDisplayText(){ return $this->displayText; }

        protected function onBeforePersist(){
            if( $this->handle === null ){
                $this->handle = Loader::helper('text')->handle($this->displayText);
            }
        }

        public static function createOrGetExisting( $mixed ){
            if( is_object($mixed) ){
                $entity = self::fetchOneBy(function(\PDO $connection, $tableName) use ($mixed){
                    $statement = $connection->prepare("SELECT * FROM {$tableName} WHERE id=:id OR displayText=:displayText OR handle=:handle");
                    $statement->bindValue(':id', (int)$mixed->id);
                    $statement->bindValue(':displayText', $mixed->displayText);
                    $statement->bindValue(':handle', $mixed->handle);
                    return $statement;
                });
                if( empty($entity)){
                    $entity = self::create($mixed);
                }
                return $entity;
            }
        }

        public function categorizeEvent( Event $eventObj ){
            $categoryID = $this->id;
            self::adhocQuery(function(\PDO $connection) use ($eventObj, $categoryID){
                $statement = $connection->prepare("INSERT INTO SchedulizerCategorizedEvents (eventID, versionID, eventCategoryID) VALUES(:eventID,:versionID,:eventCategoryID)");
                $statement->bindValue(':eventID', $eventObj->getID());
                $statement->bindValue(':versionID', $eventObj->getVersionID());
                $statement->bindValue(':eventCategoryID', $categoryID);
                return $statement;
            });
        }

        /****************************************************************
         * Fetch Methods
         ***************************************************************/

        public static function fetchAll(){
            return (array) self::fetchMultipleBy(function( \PDO $connection, $tableName ){
                $statement = $connection->prepare("SELECT * FROM {$tableName}");
                return $statement;
            });
        }

        public static function fetchCategoriesByEventID( $eventID, $versionID ){
            return (array) self::fetchMultipleBy(function(\PDO $connection, $tableName) use ($eventID, $versionID){
                $statement = $connection->prepare("SELECT sevt.* FROM SchedulizerEventCategory sevc
                    JOIN SchedulizerCategorizedEvents sece ON sevc.id = sece.eventCategoryID
                    WHERE sece.eventID = :eventID AND sece.versionID = :versionID");
                $statement->bindValue(':eventID', $eventID);
                $statement->bindValue(':versionID', $versionID);
                return $statement;
            });
        }
    }

}