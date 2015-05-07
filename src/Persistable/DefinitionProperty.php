<?php namespace Concrete\Package\Schedulizer\Src\Persistable {

    use \Concrete\Package\Schedulizer\Src\Persistable\DefinitionInspectorException;

    class DefinitionProperty extends \ReflectionProperty {

        public function __construct( \ReflectionProperty $reflectedProp, \stdClass $definition ){
            parent::__construct($reflectedProp->class, $reflectedProp->name);
            // Take the properties declared via json and set them on $this
            $_reservedProperties = array('name', 'class');
            foreach($definition AS $key => $value){
                // Since this class extends \ReflectionProperty; ensure we're not overwriting
                // any class defaults!
                if( in_array($key, $_reservedProperties) ){
                    throw DefinitionInspectorException::illegalPropertyAnnotation($reflectedProp, $key);
                }
                $this->{$key} = $value;
            }
        }

    }

}