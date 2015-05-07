<?php namespace Schedulizer\Tests\Persistable\Mocks {

    /**
     * Class InspectableOne
     * @package Schedulizer\Tests\Persistable\Mocks
     * @definition({"table":"Tablename","arbitrary":"flexible","booltype":true})
     */
    class InspectableOne {

        /** @definition({"cast":"int","declarable":false}) */
        protected $id;

        /** @definition({"cast":"datetime", "declarable":false, "autoSet":["onCreate"]}) */
        protected $createdUTC;

        /** @definition({"cast":"string", "nullable":true}) */
        protected $title;

        /** Exists but not set w/ definition! */
        protected $failOnThis;

    }

}