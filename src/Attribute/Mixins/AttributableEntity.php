<?php namespace Concrete\Package\Schedulizer\Src\Attribute\Mixins {

    use Loader;

    /**
     * Use this trait on an entity/model - the thing which you want to
     * store attributes against, and it'll decorate whatever class its
     * pulled into with all the methods necessary to interact with C5's
     * attribute system.
     *
     * Class AttributableEntity
     * @package Concrete\Package\Schedulizer\Src\Attribute\Mixins
     */
    trait AttributableEntity {

        /**
         * All dynamic calls are basically determined based on the constants defined
         * in the relevant AttributeKey class definition; this lets you access the
         * values of those constants by passing in the constant name.
         * @param $name
         * @return string
         */
        protected function attributeKeyConstant( $name ){
            return constant(sprintf('%s::%s', self::ATTR_KEY_CLASS, $name));
        }


        /**
         * If $ak is a string, load the AttributeKey object by handle and return it,
         * otherwise assume $ak was already an object and just return that. This
         * acts basically as a safety measure since so many things try to get
         * an AttributeKey object, but we don't know whats actually being passed in.
         * @param $ak string|object
         * @return \Concrete\Core\Attribute\Key\Key
         */
        protected function getAttributeKeyObjFromMixed( $ak ){
            if( !is_object($ak) ){
                return forward_static_call_array(array(self::ATTR_KEY_CLASS, 'getByHandle'), array($ak));
            }
            return $ak;
        }


        /**
         * Clear an attribute.
         * @param $ak mixed
         */
        public function clearAttribute( $ak ){
            $akObj = $this->getAttributeKeyObjFromMixed($ak);
            $avObj = $this->getAttributeValueObject($akObj);
            if( is_object($avObj) ){
                $avObj->delete();
            }
            $this->reindex();
        }


        /**
         * Set an attribute against the entity.
         * @param $ak
         * @param $value
         */
        public function setAttribute( $ak, $value ){
            $akObj = $this->getAttributeKeyObjFromMixed($ak);
            $akObj->setAttribute($this, $value);
            $this->reindex();
        }


        /**
         * Get an attribute associated with the entity.
         * @param $ak
         * @param bool $displayMode
         * @return mixed
         */
        public function getAttribute( $ak, $displayMode = false ){
            $akObj = $this->getAttributeKeyObjFromMixed($ak);
            if( is_object($akObj) ){
                $avObj = $this->getAttributeValueObject($akObj);
                if( is_object($avObj) ){
                    $args = func_get_args();
                    if( count($args) > 1 ){
                        array_shift($args);
                        return call_user_func_array(array($avObj, 'getValue'), $args);
                    }
                    return $avObj->getValue($displayMode);
                }
            }
        }


        /**
         * Render the form for an attribute.
         * @param $ak
         */
        public function getAttributeField( $ak ){
            $akObj = $this->getAttributeKeyObjFromMixed($ak);
            $akObj->render('form', $this->getAttributeValueObject($akObj));
        }


        /**
         * There are lots of dynamic method calls in here; ready carefully.
         * @param $akObj \Concrete\Core\Attribute\Key\Key
         * @param bool $createIfNotFound
         * @return bool|mixed
         */
        public function getAttributeValueObject( \Concrete\Core\Attribute\Key\Key $akObj, $createIfNotFound = false ){
            $avObj                    = false;
            $attrValueTable           = $this->attributeKeyConstant('ATTR_VALUE_TABLE');
            $attrValueTablePrimaryKey = $this->attributeKeyConstant('ENTITY_PRIMARY_KEY');
            // This usually equates to doing: $this->getID(); but sometimes the method to get an entity's ID might
            // be something like getFileID(). This allows for flexibility.
            $entityID   = call_user_func(array($this, $this->attributeKeyConstant('ENTITY_ID_ACCESSOR_METHOD')));
            $query      = sprintf("SELECT avID FROM %s WHERE %s = ? AND akID = ?", $attrValueTable, $attrValueTablePrimaryKey);
            $avID       = Loader::db()->GetOne($query, array($entityID, $akObj->getAttributeKeyID()));
            if( $avID > 0 ){
                // If attr key class is MyThingKey; this does MyThingKey::getByID($avID)
                $avObj = forward_static_call_array(array(self::ATTR_VALUE_CLASS, 'getByID'), array($avID));
                if( is_object($avObj) ){
                    $avObj->setEntity($this);
                    $avObj->setAttributeKey($akObj);
                }
            }

            if( $createIfNotFound ){
                $cnt = 0;
                // Is this avID in use?
                if( is_object($avObj) ){
                    $query = sprintf("SELECT COUNT(avID) FROM %s WHERE avID = ?", $attrValueTable);
                    $cnt = Loader::db()->GetOne($query, $avObj->getAttributeValueID());
                }
                if( !is_object($avObj) || ($cnt > 1) ){
                    $newAv = $akObj->addAttributeValue();
                    $avObj = forward_static_call_array(array(self::ATTR_VALUE_CLASS, 'getByID'), array($newAv->getAttributeValueID()));
                    $avObj->setEntity($this);
                }
            }

            return $avObj;
        }


        /**
         * Reindex attributes
         */
        public function reindex(){
            $entityID           = call_user_func(array($this, $this->attributeKeyConstant('ENTITY_ID_ACCESSOR_METHOD')));
            $searchTableName    = $this->attributeKeyConstant('ATTR_INDEXED_SEARCH_TABLE');
            $tblPrimaryKeyName  = $this->attributeKeyConstant('ENTITY_PRIMARY_KEY');
            $attribs            = forward_static_call_array(array(self::ATTR_KEY_CLASS, 'getAttributes'), array($entityID, 'getSearchIndexValue'));
            $query              = sprintf("DELETE FROM %s WHERE %s = ?", $searchTableName, $tblPrimaryKeyName);
            Loader::db()->Execute($query, array($entityID));
            $searchableAttributes = array($tblPrimaryKeyName => $entityID);
            $akClassName = self::ATTR_KEY_CLASS;
            (new $akClassName)->reindex($searchTableName, $searchableAttributes, $attribs);
        }

    }

}