<?php namespace Concrete\Package\Schedulizer\VersionUpdates {

    use Database;

    class V087 {

        protected $connection;

        public function __construct(){
            $this->connection = Database::connection(Database::getDefaultConnection())->getWrappedConnection();
        }

        public function run(){
            $this->purgeOrphanedEventTags();
        }

        /**
         * This needs to be run to clean up the SchedulizerTaggedEvents table
         * so we can setup foreign keys.
         */
        private function purgeOrphanedEventTags(){
            $this->connection->exec("DELETE FROM SchedulizerTaggedEvents WHERE NOT EXISTS (
                SELECT * FROM SchedulizerEvent WHERE SchedulizerEvent.id = SchedulizerTaggedEvents.eventID
            )");
        }

    }

}