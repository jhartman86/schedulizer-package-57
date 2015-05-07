<?php namespace Schedulizer\Tests\Persistable {

    use \Concrete\Package\Schedulizer\Src\Persistable\DefinitionInspector;

    /**
     * @package Schedulizer\Tests\Persistable
     * @group persistable
     * @todo:
     * ✓
     * ✗ Class without annotation passed to inspector throw exception
     * ✗ Property with invalid definition throws exception
     * ✗ Only annotated properties are parsed
     * ✗ Declarable property definitions filtered properly
     * ✗ Persistable property definitions filtered properly
     * ✗ Reflecting sett-able properties onto object occurs
     *   ✗ And casts properly
     * ✗ Reflecting all properties onto object occurs
     *   ✗ And casts properly
     * ✗ Casted to proper internal types: int, bool, datetime, string
     *   ✗ Feature? DateTimeZone
     * ✗ All DateTime instances are cast as UTC
     */
    class DefinitionInspectorTest extends \PHPUnit_Framework_TestCase {

        /** @var $mock1 Mocks\InspectableOne */
        protected $mock1;
        /** @var $inspectedMock1 \Concrete\Package\Schedulizer\Src\Persistable\DefinitionInspector */
        protected $inspectedMock1;

        public function setUp(){
            $this->mock1            = new Mocks\InspectableOne();
            $this->inspectedMock1   = DefinitionInspector::parse($this->mock1);
        }

        public function testInspectingMock1ReturnsInspectorInstance(){
            $this->assertInstanceOf('\Concrete\Package\Schedulizer\Src\Persistable\DefinitionInspector', $this->inspectedMock1);
        }

        public function testMock1ClassDefinition(){
            $classDefinition = $this->inspectedMock1->classDefinition();
            $this->assertInstanceOf(stdClass, $classDefinition);
            $this->assertObjectHasAttribute('table', $classDefinition);
            $this->assertObjectHasAttribute('arbitrary', $classDefinition);
            $this->assertInternalType('boolean', $classDefinition->booltype);
        }

        public function testMock1PropertyDefinitionsParsed(){
            $propertyDefinitions = $this->inspectedMock1->propertyDefinitions();
            $this->assertContainsOnlyInstancesOf(stdClass, $propertyDefinitions);
            $this->assertContains('id', array_keys($propertyDefinitions));
            $this->assertContains('createdUTC', array_keys($propertyDefinitions));
            $this->assertContains('title', array_keys($propertyDefinitions));
            $this->assertNotContains('failOnThis', array_keys($propertyDefinitions));
        }

        public function testMock1GetSinglePropertyDefinition(){
            $this->assertInstanceOf(stdClass, $this->inspectedMock1->definitionForProperty('id'));
        }

        public function testMock1GetSinglePropertyIsNullIfNotDefined(){
            $this->assertEquals(null, $this->inspectedMock1->definitionForProperty('idzasdf'));
        }

        public function testDeclarablePropertiesFiltered(){
            $definitions = $this->inspectedMock1->declarablePropertyDefinitions();
            $this->assertNotContains('id', array_keys($definitions));
            $this->assertNotContains('createdUTC', array_keys($definitions));
            $this->assertContains('title', array_keys($definitions));
        }

        public function testPersistablePropertiesFiltered(){
            $definitions = $this->inspectedMock1->persistablePropertyDefinitions();
            $this->assertNotContains('id', array_keys($definitions));
            $this->assertContains('createdUTC', array_keys($definitions));
            $this->assertContains('title', array_keys($definitions));
        }

//        public function testCreateEvent(){
//            Event::create(array(
//                'title' => 'frack ya',
//                'calendarID' => 'mk'
//            ));
//        }

//        public function testSomething(){
//            try {
//                Event::getByID(1);
//            }catch(\Exception $e){
//                echo $e->getMessage();
//            }
//
//        }

//        /**
//         * @expectedException \Concrete\Package\Schedulizer\Src\Persistable\DefinitionInspectorException
//         */
//        public function testCalendarGet(){
//            Event::getByID(1);
//        }

//        public function testCalendarCreate(){
//            $cal = Calendar::create(array(
//                'title' => 'yolodsf',
//                'ownerID' => 22
//            ));
//            print_r($cal);
//            exit;
//        }
//
//        public function testCalendarUpdate(){
//            $cal = Calendar::getByID(3);
//            if(is_object($cal)){
//                $cal->update(array('title' => 'wtf mateasdfew'));
//            }
//            print_r($cal);
//        }

//        public function testCalendarDelete(){
//            Calendar::getByID(2)->delete();
//        }

    }

}