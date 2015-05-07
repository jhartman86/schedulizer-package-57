<?php namespace Schedulizer\Tests {

    use \Concrete\Core\Package\Package;

    /**
     * Use this trait for easy access to specific things from C5. Technically
     * these should be IMMUTABLE.
     * @todo: return cloned instances all the time?
     * Class PackageTrait
     * @package Schedulizer\Tests
     */
    trait PackageTrait {

        protected $_packageInstance = null;
        protected $_packageClass    = null;

        protected function packageInstance(){
            if( $this->_packageInstance === null ){
                $this->_packageInstance = Package::getByHandle('schedulizer');
            }
            return $this->_packageInstance;
        }

        protected function packageClass(){
            if( $this->_packageClass === null ){
                $this->_packageClass = Package::getClass('schedulizer');
            }
            return $this->_packageClass;
        }

    }

}