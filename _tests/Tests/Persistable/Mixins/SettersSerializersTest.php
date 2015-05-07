<?php namespace Schedulizer\Tests\Bin\Traits {

    class AnnotatedAndImplementsTrait {
        use \Concrete\Package\Schedulizer\Src\Persistable\Mixins\SettersSerializers;
        /** @definition({"cast":"string"}) */
        public $title;
        /** @definition({"cast":"int"}) */
        public $integral;
        /** ANOTHER PROPERTY BUT NOT ANNOTATED SO SHOULD NOT BE SET */
        public $sideshow;
    }

    /**
     * Class SettersSerializersTest. There is some repitition in between these
     * tests and DefinitionInspector tests.
     * @package Schedulizer\Tests\Bin\Traits
     * @group persistable
     */
    class SettersSerializersTest extends \PHPUnit_Framework_TestCase {

        /** @var $mockObj \Concrete\Package\Schedulizer\Src\Persistable\Mixins\SettersSerializers */
        protected $mockObj;
        /** @var $annotatedMockObj AnnotatedAndImplementsTrait */
        protected $annotated;

        public function setUp(){
            $this->mockObj = $this->getObjectForTrait('Concrete\Package\Schedulizer\Src\Persistable\Mixins\SettersSerializers');
            $this->annotated = new AnnotatedAndImplementsTrait();
        }

        public function testPropertiesNotSetAgainstObjectWithoutAnnotations(){
            $this->mockObj->mergePropertiesFrom(array(
                'title'             => 'yolo'
            ));
            $this->assertObjectNotHasAttribute('title', $this->mockObj);
        }

        public function testAnnotatedClassCanHavePropertySet(){
            $this->annotated->mergePropertiesFrom(array(
                'title' => 'yolo'
            ));
            $this->assertEquals('yolo', $this->annotated->title);
        }

        public function testAnnotatedPropertyMissingAnnotationNotSet(){
            $this->annotated->mergePropertiesFrom(array(
                'sideshow' => 'lalala'
            ));
            $this->assertEquals(null, $this->annotated->sideshow);
        }

        public function testAnnotatedAsIntProperty(){
            $this->annotated->mergePropertiesFrom(array(
                'integral' => 22
            ));
            $this->assertEquals(22, $this->annotated->integral);

            // Now try and pass a string to it and see that it gets cast as integer to 0
            $this->annotated->mergePropertiesFrom(array(
                'integral' => 'lorem ipsum'
            ));
            $this->assertInternalType('integer', $this->annotated->integral);
        }

//        public function testSetPropertiesFromArray(){
//            $this->mockObj->setPropertiesFromArray(array(
//                'one' => 'value1',
//                'rpg' => array('some', 'array', 'values')
//            ));
//            $this->assertObjectHasAttribute('one', $this->mockObj);
//            $this->assertObjectHasAttribute('rpg', $this->mockObj);
//            $this->assertInternalType('string', $this->mockObj->one);
//            $this->assertInternalType('array', $this->mockObj->rpg);
//        }
//
//        public function testSetPropertiesFromObject(){
//            $this->mockObj->setPropertiesFromObject((object)array(
//                'one' => 'value1',
//                'rpg' => array('some', 'array', 'values')
//            ));
//            $this->assertObjectHasAttribute('one', $this->mockObj);
//            $this->assertObjectHasAttribute('rpg', $this->mockObj);
//            $this->assertInternalType('string', $this->mockObj->one);
//            $this->assertInternalType('array', $this->mockObj->rpg);
//        }

        public function testJsonSerialization(){
            $this->annotated->mergePropertiesFrom(array(
                'title' => 'value1',
                'integral' => array('a', 'couple', 'values')
            ));
            $serialized = json_encode($this->mockObj);
            $this->assertInternalType('string', $serialized);
        }

    }

}