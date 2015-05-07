<?php namespace Concrete\Package\Schedulizer\Src\Install {

    use Loader;
    use \DateTime;
    use \DateTimeZone;

    /**
     * Class Support
     * @package Concrete\Package\Schedulizer\Src\Install
     * @todo: mysql 5.6 for innodb full text indices
     * @todo: test for foreign key support and cascading deletes
     */
    class Support {

        const PHP_MIN_VERSION   = 5.4;

        private $db;

        /**
         * Pass in database instance
         */
        public function __construct(){
            $this->db = Loader::db();
        }

        /**
         * Easily accessible way of checking if meets system
         * requirements.
         * @return bool
         */
        public static function meetsRequirements(){
            $self = new self();
            return $self->allPassed();
        }

        /**
         * Results of all the tests.
         * @return bool
         */
        public function allPassed(){
            if( ! $this->phpVersion() ){ return false; }
            if( ! $this->mysqlHasTimezoneTables() ){ return false; }
            if( ! $this->phpDateTimeZoneConversionsCorrect() ){ return false; }
            if( ! $this->phpDateTimeSupportsOrdinals() ){ return false; }
            return true;
        }

        /**
         * Is PHP's min version available?
         * Required for using Traits
         * @return bool
         */
        public function phpVersion(){
            if( !((float) phpversion() >= self::PHP_MIN_VERSION) ){
                return false;
            }
            return true;
        }

        /**
         * Test MySQL for timezone table existence
         * @return bool
         */
        public function mysqlHasTimezoneTables(){
            if( $this->queryResult() !== '2001-01-17 07:00:00' ){
                return false;
            }
            return true;
        }

        /**
         * Test that PHP's DateTimeZone class is making the correct
         * conversions.
         * @return bool
         */
        public function phpDateTimeZoneConversionsCorrect(){
            $dto = new DateTime($this->queryResult(), new DateTimeZone('America/New_York'));
            $dto->setTimezone(new DateTimeZone('America/Denver'));
            if( $dto->format('Y-m-d H:i:s') !== '2001-01-17 05:00:00' ){
                return false;
            }
            return true;
        }

        /**
         * Test for relative word support (ordinals) in DateTime
         * classes.
         * @return bool
         */
        public function phpDateTimeSupportsOrdinals(){
            $dto = new DateTime('2001-01-17 12:00:00');
            $dto->modify('first day of this month');
            if( $dto->format('Y-m-d') !== '2001-01-01' ){
                return false;
            }
            return true;
        }

        /**
         * Issue a query that requires timezone support
         * @return mixed
         */
        protected function queryResult(){
            if( $this->_queryResult === null ){
                $this->_queryResult = $this->db->GetOne("SELECT CONVERT_TZ('2001-01-17 12:00:00', 'UTC', 'America/New_York')");
            }
            return $this->_queryResult;
        }

    }

}