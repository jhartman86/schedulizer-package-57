<?php namespace Concrete\Package\Schedulizer\Src\Persistable\Mixins {

    trait Hooks {

        // Fetching a record
        protected function onAfterFetch( $record = null ){}

        // If an object is not yet persisted
        protected function onBeforePersist(){}
        protected function onAfterPersist(){}

        // On after create (only called once, after save, if didn't exist before)
        protected function onAfterCreate(){}

        // If we're deleting
        protected function onBeforeDelete(){}
        protected function onAfterDelete(){}

    }

}