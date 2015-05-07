<?php namespace Concrete\Package\Schedulizer\Src\SystemEvents {

    use \Symfony\Component\EventDispatcher\Event AS AbstractEvent;

    class EventOnSave extends AbstractEvent {

        public function __construct( $data ){
            $this->data = $data;
        }

        /**
         * @return \Concrete\Package\Schedulizer\Src\Event
         */
        public function getEventObj(){
            return $this->data;
        }

    }

}