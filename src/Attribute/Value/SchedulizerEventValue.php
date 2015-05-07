<?php namespace Concrete\Package\Schedulizer\Src\Attribute\Value {

    use \Concrete\Package\Schedulizer\Src\Attribute\Mixins\AttributableValue;

    /**
     * Attribute value class for events. Implementation details are all imported
     * via the AttributableValue trait, and derived from the ATTR_KEY_CLASS
     * constant.
     *
     * Class SchedulizerEventValue
     * @package Concrete\Package\Schedulizer\Src\Attribute\Value
     */
    class SchedulizerEventValue extends \Concrete\Core\Attribute\Value\Value {

        use AttributableValue;

        // Define reference to the attribute key class; required by AttributableValue trait
        const ATTR_KEY_CLASS = '\Concrete\Package\Schedulizer\Src\Attribute\Key\SchedulizerEventKey';

    }

}