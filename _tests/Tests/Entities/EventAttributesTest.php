<?php //namespace Schedulizer\Tests\Entities {
//
//    use \Concrete\Core\Package\Package;
//    use \Concrete\Core\Attribute\Key\Category AS AttributeKeyCategory;
//    use \Concrete\Core\Attribute\Type AS AttributeType;
//    use Concrete\Package\Schedulizer\Controller\SinglePage\Dashboard\Schedulizer;
//    use \Concrete\Package\Schedulizer\Src\Event;
//    use \Concrete\Package\Schedulizer\Src\Attribute\Key\SchedulizerEventKey;
//
//    /**
//     * Class EventAttributesTest
//     * @package Schedulizer\Tests\Entities
//     * @group attributes
//     */
//    class EventAttributesTest extends \PHPUnit_Framework_TestCase {
//
//        public function testAddAttributeKey(){
//            $attrType = AttributeType::getByHandle('text');
//            $package  = Package::getByHandle('schedulizer');
//            $handleAndName = sprintf('testtest_%s', rand(1,10000));
//            // Return the newly created attribute item
//            return SchedulizerEventKey::add($attrType, array(
//                'akHandle' => $handleAndName,
//                'akName'   => $handleAndName
//            ), $package);
//        }
//
//        /**
//         * @todo: Ensure event w/ ID 1 exists
//         * @depends testAddAttributeKey
//         */
//        public function testAssignAttributeToAnEvent( SchedulizerEventKey $akObj ){
//            $eventObj = Event::getByID(1);
//            $eventObj->setAttribute($akObj->getAttributeKeyHandle(), 'lorem ipsum');
//            return $akObj;
//        }
//
//        /**
//         * @depends testAssignAttributeToAnEvent
//         */
//        public function testGetAttributes( SchedulizerEventKey $akObj ){
//            $attrs = SchedulizerEventKey::getAttributes(1);
//            // @todo: inspect $attrs w/ assertion
//            return $akObj;
//        }
//
//        /**
//         * @depends testGetAttributes
//         */
//        public function testDeleteAttributeKey( SchedulizerEventKey $akObj ){
//            $akObj->delete();
//        }
//
//
//
////        public function testSaveAttributeAgainstEntity(){
////            /** @var $eventObj Event */
////            $eventObj = Event::getByID(1);
////            $eventObj->setAttribute('place', rand(1,10000));
////            $attrValue = $eventObj->getAttribute('place');
////            print_r($attrValue);
////        }
//
////        public function testRegisterEntityCategory(){
////            $akc = AttributeKeyCategory::getByHandle('schedulizer_event');
////            if( ! $akc ){
////                $akc = AttributeKeyCategory::add('schedulizer_event', AttributeKeyCategory::ASET_ALLOW_MULTIPLE, Package::getByHandle('schedulizer'));
////            }
////            return $akc;
////        }
//
////        /**
////         * @depends testRegisterEntityCategory
////         */
////        public function testAssociateAttributeTypeWithCategory( $akc ){
////            $attrType = AttributeType::getByHandle('text');
////            if( $attrType ){
////                $akc->associateAttributeKeyType( $attrType );
////            }
////        }
//
//    }
//
//}