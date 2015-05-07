<?php namespace Concrete\Package\Schedulizer\Src\Persistable {

    use \DateTime;
    use \DateTimeZone;
    use \ReflectionClass;
    use \ReflectionObject;
    use \ReflectionProperty;
    use \Concrete\Package\Schedulizer\Src\Persistable\DefinitionProperty;
    use \Concrete\Package\Schedulizer\Src\Persistable\DefinitionInspectorException;

    /**
     * Class DefinitionInspector. Pay attention to the fact that this EXTENDS CORE
     * REFLECTIONCLASS.
     * @package Concrete\Package\Schedulizer\Src\Persistable
     */
    class DefinitionInspector extends \ReflectionClass {

        protected static $parsedCache = array();

        protected $_propertyDefinitions;
        protected $_classDefinition;
        protected $_declarablePropertyDefinitions;
        protected $_persistablePropertyDefinitions;

        /**
         * @param $mixed Object or class name
         * @return DefinitionInspector
         */
        public static function parse( $mixed ){
            $klass = is_object($mixed) ? get_class($mixed) : $mixed;
            if( ! self::$parsedCache[$klass] ){
                self::$parsedCache[$klass] = new self($mixed);
            }
            return self::$parsedCache[$klass];
        }

        /**
         * Analyze comments marked as @definition({ ..VALID_JSON.. }) on class
         * @return mixed|null
         * @throws DefinitionInspectorException
         */
        public function classDefinition(){
            if( $this->_classDefinition === null ){
                if( preg_match('/@definition\((.*)\)/', $this->getDocComment(), $def) ){
                    $this->_classDefinition = json_decode($def[1]);
                }else{
                    throw DefinitionInspectorException::classNotAnnotated($this);
                }
            }
            return $this->_classDefinition;
        }

        /**
         * Get the definition for a specific property.
         * @param $propertyName
         * @return mixed
         * @throws DefinitionInspectorException
         */
        public function definitionForProperty( $propertyName ){
            return $this->propertyDefinitions()[$propertyName];
        }

        /**
         * Analyze comments marked as @definition({ ..VALID_JSON.. }) on properties
         * @param \Closure callback You can pass a function into this method which will
         * receive the list of ALL property definitions, and you can filter further from
         * that. Then the return value of the callback (which should return a filtered
         * list of propertyDefinitions) will be returned instead of the full list.
         * @return array
         * @throws DefinitionInspectorException
         */
        public function propertyDefinitions(){
            if( $this->_propertyDefinitions === null ){
                $this->_propertyDefinitions = array();
                foreach( $this->getProperties() AS $reflProp ){
                    // isDefault() checks whether prop declared at compile vs runtime
                    if( $reflProp->isDefault() ){
                        if( preg_match('/@definition\((.*)\)/', $reflProp->getDocComment(), $def) ){
                            /** @var $declaration stdObject */
                            $definition = json_decode($def[1]);
                            if( ! $definition ){
                                throw DefinitionInspectorException::invalidPropertyAnnotation($reflProp);
                            }
                            // Push onto list
                            $this->_propertyDefinitions[$reflProp->getName()] = new DefinitionProperty($reflProp, $definition);
                        }
                    }
                }
            }

            return $this->_propertyDefinitions;
        }

        /**
         * Using the already parsed property definitions, filter out properties
         * that should not have their values set on an instance (eg. id, modifiedUTC)
         * @return array
         */
        public function declarablePropertyDefinitions(){
            if( $this->_declarablePropertyDefinitions === null ){
                $this->_declarablePropertyDefinitions = array_filter($this->propertyDefinitions(),
                    function( $definition ){
                        if( !($definition->declarable === false) ){
                            return true;
                        }
                    }
                );
            }
            return $this->_declarablePropertyDefinitions;
        }

        /**
         * Using the already parsed property definitions, filter out properties
         * that should not have their values passed to the database on persisting
         * (eg. id)
         * @return array
         */
        public function persistablePropertyDefinitions(){
            if( $this->_persistablePropertyDefinitions === null ){
                $this->_persistablePropertyDefinitions = array_filter($this->propertyDefinitions(),
                    function( $definition ){
                        if( ($definition->declarable === false) && empty($definition->autoSet) ){
                            return false;
                        }
                        return true;
                    }
                );
            }

            return $this->_persistablePropertyDefinitions;
        }

        /**
         * Properties that can be set on an object (eg. NOT id)
         * @param $object
         * @param array $data
         */
        public function reflectSettablesOntoInstance( $object, $data = array() ){
            $this->reflectWith($this->declarablePropertyDefinitions(), $object, $data);
        }

        /**
         * Reflect onto any property (ignore declarable restriction; for
         * internal use like setting ID and such)
         * @param $object
         * @param array $data
         * @internal
         */
        public function reflectAllOntoInstance( $object, $data = array() ){
            $this->reflectWith($this->propertyDefinitions(), $object, $data);
        }

        /**
         * List of $properties to set, against which $object, merging what $data
         * @param $properties
         * @param $object
         * @param $data
         */
        protected function reflectWith( $properties, $object, $data ){
            $reflection = new ReflectionObject($object);
            $data       = (is_object($data)) ? $data : (object) $data;

            foreach($properties AS $prop => $definition){
                if( property_exists($data, $prop) ){
                    $property = $reflection->getProperty($prop);
                    $property->setAccessible(true);
                    $this->setReflectedPropertyOnObject($property, $definition, $object, $data->{$prop});
                }
            }
        }

        /**
         * Looks at the property $definition->cast value, and determines how to
         * handle setting the value on an object.
         * @param ReflectionProperty $property
         * @param $definition
         * @param $object
         * @param $value
         */
        protected function setReflectedPropertyOnObject( ReflectionProperty $property, $definition, $object, $value ){
            $dtzUTC = new DateTimeZone('UTC');

            if( $definition->nullable === true && ($value === null) ){
                $property->setValue($object, null);
                return;
            }

            $castedValue = null;

            switch( $definition->cast ){
                case 'int':
                    $castedValue = (int)$value; break;

                case 'bool':
                    $castedValue = (bool)(int)$value; break;

                case 'datetime':
                    if( $value instanceof DateTime ){
                        $value->setTimezone($dtzUTC);
                        $castedValue = $value;
                        break;
                    }
                    $castedValue = new DateTime($value, $dtzUTC);
                    break;

                case 'string':
                    $castedValue = $value; break;
            }

            $property->setValue($object, $castedValue);
        }

    }

}