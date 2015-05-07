<?php namespace Concrete\Package\Schedulizer\Src\Attribute\Key {

    use \Concrete\Package\Schedulizer\Src\Attribute\Mixins\AttributableKey;

    /**
     * Attribute key class for events. Implementation details are all imported
     * via the AttributeKey trait.
     *
     * Class SchedulizerEventKey
     * @package Concrete\Package\Schedulizer\Src\Attribute\Key
     */
    class SchedulizerEventKey extends \Concrete\Core\Attribute\Key\Key {

        // Attributes are the PERFECT application for traits...
        use AttributableKey;

        /**
         * All the following constants MUST be defined, then the AttributableKey
         * trait can parse/determine all the necessary stuff for working with
         * attributes automatically. Way less setup :)
         */
        const ATTR_CATEGORY_HANDLE          = 'schedulizer_event';
        const ATTR_VALUE_TABLE              = 'SchedulizerEventAttributeValues';
        const ATTR_VALUE_CLASS              = '\Concrete\Package\Schedulizer\Src\Attribute\Value\SchedulizerEventValue';
        const ATTR_CACHE_KEY_PREFIX         = 'schedulizer_event_key_handle';
        const ENTITY_PRIMARY_KEY            = 'eventID';
        const ENTITY_ID_ACCESSOR_METHOD     = 'getID';
        const ATTR_INDEXED_SEARCH_TABLE     = 'SchedulizerEventSearchIndexAttributes';

    }

}