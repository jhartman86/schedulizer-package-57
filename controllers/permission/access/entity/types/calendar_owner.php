<?php namespace Concrete\Package\Schedulizer\Controller\Permission\Access\Entity\Types {

    use Loader;
    use \Concrete\Package\Schedulizer\Src\Permission\Access\Entity\CalendarOwnerEntity AS CalendarOwnerPermissionAccessEntity;

    class CalendarOwner extends \Concrete\Core\Controller\Controller {

        protected $tokenIsValid = false;

        public function __construct(){
            parent::__construct();
            $this->tokenIsValid = Loader::helper('validation/token')->validate('process');
        }

        public function view(){
            if( ! $this->tokenIsValid ){ return; }

            $js = Loader::helper('json');
            $obj = new \stdClass;
            $pae = CalendarOwnerPermissionAccessEntity::getOrCreate();
            $obj->peID = $pae->getAccessEntityID();
            $obj->label = $pae->getAccessEntityLabel();
            print $js->encode($obj);
        }

    }

}