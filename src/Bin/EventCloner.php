<?php namespace Concrete\Package\Schedulizer\Src\Bin {

    use \Concrete\Package\Schedulizer\Src\Event;
    use \Concrete\Package\Schedulizer\Src\Attribute\Key\SchedulizerEventKey;

    class EventCloner {

        protected $originalEventObj;
        protected $clonedEventObj;

        public static function with( Event $eventObj ){
            $self = new self($eventObj);
            return $self->clonedEventObj;
        }

        protected function __construct( Event $eventObj ){
            if( ! $eventObj->isPersisted() ){
                throw new \Exception('From EventCloner: Event must already exist.');
            }
            $this->originalEventObj = $eventObj;
            $this->cloneEvent()
                 //->cloneEventTimes()
                 //->assignTagsToClonedEvent()
                 ->assignAttribtuesToClonedEvent();
        }

        /**
         * Clone the event record.
         * @return $this
         */
        protected function cloneEvent(){
            $this->clonedEventObj = $this->cloneInMemoryAndSetProps($this->originalEventObj, array(
                'id' => null
            ));
            $this->clonedEventObj->save();
            return $this;
        }

        /**
         * Clone event time(s) and all subsequent related properties.
         * @return $this
         */
        protected function cloneEventTimes(){
            $eventTimes = $this->originalEventObj->getEventTimes();
            /** @var $originalEventTimeObj \Concrete\Package\Schedulizer\Src\EventTime */
            foreach($eventTimes AS $originalEventTimeObj){
                // First clone the event time record itself
                $clonedEventTime = $this->cloneInMemoryAndSetProps($originalEventTimeObj, array(
                    'id'        => null,
                    'eventID'   => $this->clonedEventObj->getID()
                ));
                $clonedEventTime->save();

                // Now clone any nullifiers from the original eventTimeObj to the cloned
                $originalTimeNullifiers = $originalEventTimeObj->getEventTimeNullifiers();
                foreach($originalTimeNullifiers AS $nullifierObj){
                    $clonedNullifier = $this->cloneInMemoryAndSetProps($nullifierObj, array(
                        'id'          => null,
                        'eventTimeID' => $clonedEventTime->getID()
                    ));
                    $clonedNullifier->save();
                }

                // Clone eventTimeWeekdays on timeIDs (no object methods for these, so straight in the 'base)
                $statement = $this->dbConnection()->prepare("SELECT repeatWeeklyDay FROM SchedulizerEventTimeWeekdays WHERE eventTimeID=:eventTimeID");
                $statement->bindValue(':eventTimeID', $originalEventTimeObj->getID());
                $statement->execute();
                $records = $statement->fetchAll(\PDO::FETCH_OBJ);
                if( ! empty($records) ){
                    foreach($records AS $stdObj){
                        $insertStatement = $this->dbConnection()->prepare("INSERT INTO SchedulizerEventTimeWeekdays (eventTimeID, repeatWeeklyDay) VALUES (:eventTimeID, :repeatWeeklyDay)");
                        $insertStatement->bindValue(':eventTimeID', $clonedEventTime->getID());
                        $insertStatement->bindValue(':repeatWeeklyDay', $stdObj->repeatWeeklyDay);
                        $insertStatement->execute();
                    }
                }
            }
            return $this;
        }

        /**
         * We can't use the event tag object in here because we're not modifying any
         * tags, just cloning the association records.
         * @return $this
         */
        protected function assignTagsToClonedEvent(){
            $statement = $this->dbConnection()->prepare("SELECT eventTagID FROM SchedulizerTaggedEvents WHERE eventID=:eventID");
            $statement->bindValue(':eventID', $this->originalEventObj->getID());
            $statement->execute();
            $records = $statement->fetchAll(\PDO::FETCH_OBJ);
            if( ! empty($records) ){
                foreach($records AS $stdObj){
                    $insertStatement = $this->dbConnection()->prepare("INSERT INTO SchedulizerTaggedEvents (eventID, eventTagID) VALUES (:eventID, :eventTagID)");
                    $insertStatement->bindValue(':eventID', $this->clonedEventObj->getID());
                    $insertStatement->bindValue(':eventTagID', $stdObj->eventTagID);
                    $insertStatement->execute();
                }
            }

            return $this;
        }

        /**
         * Duplicate all attributes from the original to the clone
         * @return $this
         */
        protected function assignAttribtuesToClonedEvent(){
            $attrList = SchedulizerEventKey::getList();


            return $this;
        }

        /**
         * Clone and adjust the properties on an object.
         * @param $mixed
         * @param array $properties
         */
        protected function cloneInMemoryAndSetProps( $mixed, $properties = array() ){
            $cloned     = clone $mixed;
            $reflection = new \ReflectionObject($mixed);
            $propNames  = array_keys($properties);
            foreach($propNames AS $name){
                $reflProp = $reflection->getProperty($name);
                $reflProp->setAccessible(true);
                $reflProp->setValue($cloned, $properties[$name]);
            }
            return $cloned;
        }

        /**
         * @return \PDO
         */
        protected function dbConnection(){
            if( $this->_dbConnection === null ){
                $this->_dbConnection = \Core::make('SchedulizerDB');
            }
            return $this->_dbConnection;
        }

    }

}