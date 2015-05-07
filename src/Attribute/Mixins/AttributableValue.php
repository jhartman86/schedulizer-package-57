<?php namespace Concrete\Package\Schedulizer\Src\Attribute\Mixins {

    use Loader;

    trait AttributableValue {

        protected $entityObj;

        /**
         * @param $name
         * @return mixed
         */
        protected function attributeKeyConstant( $name ){
            return constant(sprintf('%s::%s', self::ATTR_KEY_CLASS, $name));
        }

        /**
         * @param $entity
         */
        public function setEntity( $entity ){
            $this->entityObj = $entity;
        }

        /**
         * @return mixed
         */
        public function getEntity(){
            return $this->entityObj;
        }

        /**
         * @param $avID
         * @return AttributableValue
         */
        public static function getByID( $avID ){
            $eav = new static();
            $eav->load($avID);
            if( $eav->getAttributeValueID() === $avID ){
                return $eav;
            }
        }

        /**
         * Delete an entity value.
         * @return void
         */
        public function delete(){
            $db = Loader::db();
            $db->Execute(
                sprintf("DELETE FROM %s WHERE %s = ? AND akID = ? AND avID = ?", $this->attributeKeyConstant('ATTR_VALUE_TABLE'), $this->attributeKeyConstant('ENTITY_PRIMARY_KEY')),
                array(
                    call_user_func(array($this->entityObj, $this->attributeKeyConstant('ENTITY_ID_ACCESSOR_METHOD'))),
                    $this->attributeKey->getAttributeKeyID(),
                    $this->getAttributeValueID()
                )
            );
            // Before we run delete() on the parent object, we make sure that attribute value isn't being referenced in the table anywhere else
            $num = $db->GetOne(sprintf("SELECT COUNT(avID) FROM %s WHERE avID = ?", $this->attributeKeyConstant('ATTR_VALUE_TABLE')), array($this->getAttributeValueID()));
            if ($num < 1) {
                parent::delete();
            }
        }

    }

}