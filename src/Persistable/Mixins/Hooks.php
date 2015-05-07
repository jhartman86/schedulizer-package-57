<?php namespace Concrete\Package\Schedulizer\Src\Persistable\Mixins {

    trait Hooks {

        // Fetching a record
        protected function onAfterFetch( $record = null ){}

        // If an object is not yet persisted
        protected function onBeforePersist(){}
        protected function onAfterPersist(){}

        // If we're deleting
        protected function onBeforeDelete(){}
        protected function onAfterDelete(){}

    }

}