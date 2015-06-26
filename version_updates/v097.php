<?php namespace Concrete\Package\Schedulizer\VersionUpdates {

    use Database;
    use PermissionKey; /** @see \Concrete\Core\Permission\Key\Key */

    class V097 {

        protected $connection;

        public function __construct(){
            $this->connection = Database::connection(Database::getDefaultConnection())->getWrappedConnection();
        }

        public function run(){
            $this->renameAddEventToEditEventsPermissions();
        }

        /**
         * This needs to be run to clean up the SchedulizerTaggedEvents table
         * so we can setup foreign keys.
         */
        private function renameAddEventToEditEventsPermissions(){
            $existingPk = PermissionKey::getByHandle('add_events');
            if( is_object($existingPk) ){
                $pkID = $existingPk->getPermissionKeyID();
                $this->connection->exec(sprintf("UPDATE PermissionKeys SET pkHandle = 'edit_events', pkName = 'Edit Events', pkDescription = 'Can Add and Update Calendar Events' WHERE pkID = %s", $pkID));
            }
        }

    }

}