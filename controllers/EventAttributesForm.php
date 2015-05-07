<?php namespace Concrete\Package\Schedulizer\Controller {

    use Request;
    use \Concrete\Package\Schedulizer\Src\Event;

    class EventAttributesForm extends \Concrete\Core\Controller\Controller {

        protected $viewPath = 'event_attributes_form';

        public function __construct(){
            parent::__construct();
        }

        /**
         * Pass an existing or new event obj to the view on render
         * @param null $id
         */
        public function view( $id = null ){
            if( ! empty($id) ){
                $this->set('eventObj', Event::getByID($id));
                return;
            }
            $this->set('eventObj', new Event());
        }

    }

}