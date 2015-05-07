<?php namespace Concrete\Package\Schedulizer\Src\Attribute\Mixins {

    use Loader;
    use CacheLocal;
    use \Concrete\Core\Attribute\Value\ValueList as AttributeValueList;

    /**
     * Implementing classes MUST define the following constants. The values below
     * are just examples, but pretend you're working with an entity named 'Subscriptions'
     * in your package 'food_world'.
     *
     *  ATTR_CATEGORY_HANDLE        = 'food_world_subscriptions'
     *  ATTR_VALUE_TABLE            = 'FoodWorldSubscriptionAttributeValues'
     *  ATTR_VALUE_CLASS            = '\Concrete\Package\FoodWorld\Src\Attribute\Value\FoodWorldSubscriptionValue'
     *  ATTR_CACHE_KEY_PREFIX       = 'food_world_key_handle'
     *  ENTITY_PRIMARY_KEY          = 'subscriptionID'
     *  ENTITY_ID_ACCESSOR_METHOD   = 'getID'
     *  ATTR_INDEXED_SEARCH_TABLE   = 'FoodWorldSubscriptionSearchIndexAttributes'
     *
     * @note: the ENTITY_PRIMARY_KEY constant refers to the column name you setup
     * in FoodWorldSubscriptionAttributeValues table, NOT the name of the column
     * in your implementing model/entity class. That's what the ENTITY_ID_ACCESSOR_METHOD
     * is for. So for example, if you have a subscription entity, and to get its ID
     * the method is $subscription->getSubscriptionID() instead of just ->getID(),
     * then you should set ENTITY_ID_ACCESSOR_METHOD = 'getSubscriptionID'.
     *
     * @package Concrete\Package\Schedulizer\Src\Attribute\Mixins
     */
    trait AttributableKey {

        /**
         * Used to define the searchIndex table.
         * @var array
         */
        protected $searchIndexFieldDefinition = array(
            'columns' => array(
                array('name' => self::ENTITY_PRIMARY_KEY, 'type' => 'integer', 'options' => array('unsigned' => true, 'default' => 0, 'notnull' => true))
            ),
            'primary' => array(self::ENTITY_PRIMARY_KEY)
        );


        /**
         * Get search table name. Note, when you setup an attribute_key_category, it'll
         * automatically generated the table for you based on the definition here.
         * @return string
         */
        public function getIndexedSearchTable(){
            return self::ATTR_INDEXED_SEARCH_TABLE;
        }


        /**
         * Get an attribute key object by ID.
         * @param $akID
         * @return AttributableKey
         */
        public static function getByID( $akID ){
            $ak = new static();
            $ak->load($akID);
            if( $ak->getAttributeKeyID() > 0 ){
                return $ak;
            }
        }


        /**
         * @param $identifier
         * @param string $method
         * @return AttributeValueList
         */
        public static function getAttributes( $identifier, $method = 'getValue' ){
            $values = Loader::db()->GetAll(
                sprintf("SELECT akID, avID FROM %s WHERE %s = ?", self::ATTR_VALUE_TABLE, self::ENTITY_PRIMARY_KEY),
                array($identifier)
            );
            $avl = new AttributeValueList();
            foreach($values AS $val){
                $ak = self::getByID($val['akID']);
                if( is_object($ak) ){
                    $value = $ak->getAttributeValue($val['avID'], $method);
                    $avl->addAttributeValue($ak, $value);
                }
            }
            return $avl;
        }


        /**
         * @param $avID
         * @param string $method
         * @return mixed
         */
        public function getAttributeValue( $avID, $method = 'getValue' ){
            $av = forward_static_call_array(array(self::ATTR_VALUE_CLASS, 'getByID'), array($avID));
            if(is_object($av)){
                $av->setAttributeKey($this);
                return $av->{$method}();
            }
        }


        /**
         * @param $akHandle
         * @return bool|AttributableKey|int
         */
        public static function getByHandle($akHandle){
            $ak = CacheLocal::getEntry(self::ATTR_CACHE_KEY_PREFIX, $akHandle);
            if( is_object($ak) ){
                return $ak;
            }elseif($ak == -1){
                return false;
            }

            $ak = -1;
            $q = sprintf("SELECT ak.akID FROM AttributeKeys ak INNER JOIN AttributeKeyCategories akc ON ak.akCategoryID = akc.akCategoryID
            WHERE ak.akHandle = ? AND akc.akCategoryHandle = '%s'", self::ATTR_CATEGORY_HANDLE);
            $akID = Loader::db()->GetOne($q, array($akHandle));

            if( $akID > 0 ){
                $ak = self::getByID($akID);
            }

            CacheLocal::set(self::ATTR_CACHE_KEY_PREFIX, $akHandle, $ak);
            return $ak;
        }


        /**
         * @param $entityObj
         * @param bool $value
         */
        protected function saveAttribute( $entityObj, $value = false ){
            // We check a cID/cvID/akID combo, and if that particular combination has an attribute value ID that
            // is NOT in use anywhere else on the same cID, cvID, akID combo, we use it (so we reuse IDs)
            // otherwise generate new IDs
            $av = $entityObj->getAttributeValueObject($this, true);
            parent::saveAttribute($av, $value);
            Loader::db()->Replace(self::ATTR_VALUE_TABLE, array(
                self::ENTITY_PRIMARY_KEY => call_user_func(array($entityObj, self::ENTITY_ID_ACCESSOR_METHOD)),
                'akID' => $this->getAttributeKeyID(),
                'avID' => $av->getAttributeValueID()
            ), array(self::ENTITY_PRIMARY_KEY, 'akID'));

            call_user_func(array($entityObj, 'reindex'));
            // necessary?
            unset($av);
            unset($entityObj);
        }


        /**
         * @param $at
         * @param $args
         * @param bool $pkg
         * @return mixed
         */
        public static function add($at, $args, $pkg = false){
            CacheLocal::delete(self::ATTR_CACHE_KEY_PREFIX, $args['akHandle']);
            $ak = parent::add(self::ATTR_CATEGORY_HANDLE, $at, $args, $pkg);
            return $ak;
        }


        /**
         * Delete an attribute key.
         */
        public function delete(){
            parent::delete();
            $db = Loader::db();
            $r = $db->Execute(sprintf("SELECT avID FROM %s WHERE akID = ?", self::ATTR_VALUE_TABLE), array(
                $this->getAttributeKeyID()
            ));
            while($row = $r->FetchRow()){
                $db->Execute("DELETE FROM AttributeValues WHERE avID = ?", array($row['avID']));
            }
            $db->Execute(sprintf("DELETE FROM %s WHERE akID = ?", self::ATTR_VALUE_TABLE), array($this->getAttributeKeyID()));
        }


        /**
         * @param $akID
         * @return AttributableKey
         * @todo: is this deprecated / no longer necessary?
         */
        public function get( $akID ){
            return self::getByID($akID);
        }


        /**
         * @return mixed
         */
        public static function getColumnHeaderList(){
            return parent::getList(self::ATTR_CATEGORY_HANDLE, array('akIsColumnHeader' => 1));
        }


        /**
         * @return mixed
         */
        public static function getList() {
            return parent::getList(self::ATTR_CATEGORY_HANDLE);
        }


        /**
         * @return mixed
         */
        public static function getSearchableList() {
            return parent::getList(self::ATTR_CATEGORY_HANDLE, array('akIsSearchable' => 1));
        }


        /**
         * @return mixed
         */
        public static function getSearchableIndexedList() {
            return parent::getList(self::ATTR_CATEGORY_HANDLE, array('akIsSearchableIndexed' => 1));
        }


        /**
         * @return mixed
         */
        public static function getImporterList() {
            return parent::getList(self::ATTR_CATEGORY_HANDLE, array('akIsAutoCreated' => 1));
        }


        /**
         * @return mixed
         */
        public static function getUserAddedList() {
            return parent::getList(self::ATTR_CATEGORY_HANDLE, array('akIsAutoCreated' => 0));
        }

    }

}