<?php namespace Concrete\Package\Schedulizer\Src\Persistable\Contracts {

    /**
     * @package Concrete\Package\Schedulizer\Src\Persistable
     */
    interface InterfacePersistable {
        public static function getByID( $id );
        public static function create( $data );
        public function update( $data );
        public function save();
        public function delete();
    }

    /**
     * Class Persistable
     * @package Concrete\Package\Schedulizer\Src\Abstracts
     */
    abstract class Persistant implements InterfacePersistable, \JsonSerializable {

        const PACKAGE_HANDLE    = 'schedulizer';
        const TIMESTAMP_FORMAT  = 'Y-m-d H:i:s';
        const DEFAULT_TIMEZONE  = 'UTC';

    }

}