<?php namespace Concrete\Package\Schedulizer\Src\Persistable\Mixins {

    use Concrete\Package\Schedulizer\Src\Persistable\DefinitionInspector;

    trait SettersSerializers {

        /**
         * @param $mixed
         * @return $this
         */
        public function mergePropertiesFrom( $mixed ){
            if( is_array($mixed) || is_object($mixed) ){
                DefinitionInspector::parse($this)->reflectSettablesOntoInstance($this, $mixed);
            }
            return $this;
        }


        /**
         * Return properties for JSON serialization
         * @return array|mixed
         */
        public function jsonSerialize(){
            return (object) get_object_vars($this);
        }

    }

}