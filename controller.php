<?php namespace Concrete\Package\Schedulizer {
    defined('C5_EXECUTE') or die(_("Access Denied."));

    /** @link https://github.com/concrete5/concrete5-5.7.0/blob/develop/web/concrete/config/app.php#L10-L90 Aliases */
    //use Package; /** @see \Concrete\Core\Package\Package */
    use Database;
    use Config; /** @see \Concrete\Core */
    use Loader; /** @see \Concrete\Core\Legacy\Loader */
    use BlockType; /** @see \Concrete\Core\Block\BlockType\BlockType */
    use SinglePage; /** @see \Concrete\Core\Page\Single */
    use Route;
    use Router;
    use \DateTime;
    use \DateTimeZone;
    use \Concrete\Core\Attribute\Key\Category AS AttributeKeyCategory;
    use \Concrete\Core\Attribute\Type AS AttributeType;
    use \Concrete\Package\Schedulizer\Src\Api\ApiOnStart;
    use PermissionKeyCategory; /** @see \Concrete\Core\Permission\Category */
    use \Concrete\Core\Permission\Access\Entity\Type AS PermissionAccessEntityType;
    use \Concrete\Package\Schedulizer\Src\Permission\Key\SchedulizerKey AS SchedulizerPermissionKey;
    use \Concrete\Package\Schedulizer\Src\Permission\Key\SchedulizerCalendarKey AS SchedulizerCalendarPermissionKey;
    use Events;

    /**
     * Class Controller
     * @package Concrete\Package\Schedulizer
     * Make Doctrine suck less: http://labs.octivi.com/mastering-symfony2-performance-doctrine/
     */
    class Controller extends \Concrete\Core\Package\Package {

        const PACKAGE_HANDLE                    = 'schedulizer',
              // Config keys
              CONFIG_DEFAULT_TIMEZONE           = 'default_timezone',
              CONFIG_EVENT_AUTOGENERATE_PAGES   = 'autogenerate_pages',
              CONFIG_EVENT_PAGE_PARENT          = 'parent_page_id',
              CONFIG_EVENT_PAGE_TYPE            = 'event_page_type';

        protected static $configDefaults        = array(
            self::CONFIG_DEFAULT_TIMEZONE           => 'UTC',
            self::CONFIG_EVENT_AUTOGENERATE_PAGES   => 0,
            self::CONFIG_EVENT_PAGE_PARENT          => null,
            self::CONFIG_EVENT_PAGE_TYPE            => null
        );

        protected $pkgHandle                = self::PACKAGE_HANDLE;
        protected $appVersionRequired       = '5.7.3.2';
        protected $pkgVersion               = '0.57';

        public function getPackageName(){ return t('Schedulizer'); }
        public function getPackageDescription(){ return t('Schedulizer Calendar Package'); }


        /**
         * C5's routing is hacked such that it doesn't mix into symphony's API, so we
         * override the whole thing here with our own routing detection PRIOR to letting
         * the C5 router run...
         * @todo: with the symfony router in here potentially exiting after
         * processing an API call, and subsequently cancelling any other stuff
         * from being processed by C5, other packages or things that
         * listen for dispatched events MAY NOT GET RUN :(
         */
        public function on_start(){
            define('SCHEDULIZER_IMAGE_PATH', DIR_REL . '/packages/' . $this->pkgHandle . '/images/');

            // Composer Autoloader
            require __DIR__ . '/vendor/autoload.php';

            // @todo: add installation support tests for current timezone and provide
            // notifications, and test the implications of using this!
            if( @date_default_timezone_get() !== 'UTC' ){
                @date_default_timezone_set('UTC');
            }

            // These have to occur in a specific order!
            $this->setupClassBindings()
                 ->setupApiRoutes()
                 ->setupC5Routes();
        }


        /**
         * @return $this
         */
        protected function setupClassBindings(){
            // Make the package-specific entity manager accessible via "make"; Note that
            // passing TRUE as the last argument to bind() has the effect of registering
            // in the service container as a singleton!
            \Core::bind('SchedulizerDB', function(){
                return Database::connection(Database::getDefaultConnection())->getWrappedConnection();
            }, true);

            // Core file's \Concrete\Core\Permission\Access\Access getByID() method doesn't
            // account for namespacing to packages, so we have to bind this here.
            \Core::bind('\\Concrete\\Core\\Permission\\Access\\SchedulizerAccess', '\\Concrete\\Package\\Schedulizer\\Src\\Permission\\Access\\SchedulizerAccess');
            \Core::bind('\\Concrete\\Core\\Permission\\Access\\SchedulizerCalendarAccess', '\\Concrete\\Package\\Schedulizer\\Src\\Permission\\Access\\SchedulizerCalendarAccess');

            // Same thing but for Calendar Owner Access Entity Type
            \Core::bind('\\Concrete\\Core\\Permission\\Access\\Entity\\CalendarOwnerEntity', '\\Concrete\\Package\\Schedulizer\\Src\\Permission\\Access\\Entity\\CalendarOwnerEntity');

            return $this;
        }


        /**
         * In order to define a proper RESTful API, we can't use C5's
         * Router/Route classes as they don't properly extend Symfony's
         * routing class. This registers an API that is, technically, going
         * around everything C5 related; it will exit after its run if a
         * route is being handled before anything else C5 related gets run.
         * @return $this
         */
        protected function setupApiRoutes(){
            ApiOnStart::execute(function( $apiOnStart ){
                /** @var $apiOnStart \Concrete\Package\Schedulizer\Src\Api\OnStart */
                // GET,POST,PUT,DELETE
                $apiOnStart->addRoute('calendar', 'CalendarResource');
                // GET,POST,PUT,DELETE
                $apiOnStart->addRoute('event', 'EventResource');
                // GET,POST,DELETE
                $apiOnStart->addRoute('event_time_nullify', 'EventTimeNullifyResource');
                // GET,POST,PUT,DELETE
                $apiOnStart->addRoute('event_tags', 'EventTagsResource');
                // GET
                $apiOnStart->addRoute('event_list', 'EventListResource');
                // GET
                $apiOnStart->addRoute('timezones', 'TimezoneResource');
            });

            return $this;
        }


        /**
         * Normal old ajax calls using C5's routing mechanism.
         */
        protected function setupC5Routes(){
            // Note: C5's router fails to implement the full
            // Symfony routing options (hence why we customize the API stuff above),
            // so to pass an optional parameter we have to register the route twice :(
            Route::register(
                Router::route(array('event_attributes_form/{id}', self::PACKAGE_HANDLE)),
                '\Concrete\Package\Schedulizer\Controller\EventAttributesForm::view'
            );

            Route::register(
                Router::route(array('event_attributes_form', self::PACKAGE_HANDLE)),
                '\Concrete\Package\Schedulizer\Controller\EventAttributesForm::view'
            );

            // Permission dialogs
            Route::register(
                Router::route(array('permission/dialog/schedulizer', self::PACKAGE_HANDLE)),
                '\Concrete\Package\Schedulizer\Controller\Permission\Dialog\Schedulizer::view'
            );

            Route::register(
                Router::route(array('permission/dialog/schedulizer_calendar', self::PACKAGE_HANDLE)),
                '\Concrete\Package\Schedulizer\Controller\Permission\Dialog\SchedulizerCalendar::view'
            );

            // Permission Category routes
            Route::register(
                Router::route(array('permission/category/schedulizer', self::PACKAGE_HANDLE)),
                '\Concrete\Package\Schedulizer\Controller\Permission\Category\Schedulizer::view'
            );

            Route::register(
                Router::route(array('permission/category/schedulizer_calendar', self::PACKAGE_HANDLE)),
                '\Concrete\Package\Schedulizer\Controller\Permission\Category\SchedulizerCalendar::view'
            );

            // Calendar Owner permissionable entity type
            Route::register(
                Router::route(array('permission/access/entity/types/calendar_owner', self::PACKAGE_HANDLE)),
                'Concrete\Package\Schedulizer\Controller\Permission\Access\Entity\Types\CalendarOwner::view'
            );

            return $this;
        }


        public function uninstall(){
            parent::uninstall();

            // Uninstall permission key categories
            /** @var $pkc1 \Concrete\Core\Permission\Category */
//            if( $pkc1 = PermissionKeyCategory::getByHandle('schedulizer') ){
//                $pkc1->delete();
//            }
//
//            /** @var $pkc2 \Concrete\Core\Permission\Category */
//            if( $pkc2 = PermissionKeyCategory::getByHandle('schedulizer_calendar') ){
//                $pkc2->delete();
//            }

            $tables   = array(
                'btSchedulizer',
                'btSchedulizerEvent',
                'SchedulizerCalendar',
                'SchedulizerEvent',
                'SchedulizerEventVersion',
                'SchedulizerEventTag',
                'SchedulizerTaggedEvents',
                'SchedulizerEventTime',
                'SchedulizerEventTimeWeekdays',
                'SchedulizerEventTimeNullify',
                'SchedulizerEventAttributeValues',
                'SchedulizerEventSearchIndexAttributes'
            );
            try {
                $database = Loader::db();
                $database->Execute(sprintf("SET foreign_key_checks = 0; DROP TABLE IF EXISTS %s; SET foreign_key_checks = 1", join(',', $tables)));
            }catch(\Exception $e){ /* do nothing */ }
        }


        /**
         * @return void
         * @throws mixed
         */
        public function upgrade(){
            if( Src\Install\Support::meetsRequirements() ){
                parent::upgrade();
                $this->installAndUpdate();
                return;
            }
            throw new Exception("System requirements not met.");
        }


        /**
         * This also takes care of handling input data from the
         * installation options.
         * @return void
         * @throws mixed
         */
        public function install() {
            if( Src\Install\Support::meetsRequirements() ){
                $this->_packageObj = parent::install();
                $this->saveConfigsFromInstallScreen()
                     ->installAndUpdate();
                return;
            }
            throw new Exception("System requirements not met.");
        }


        /**
         * During installation only, we show the screen with support
         * tests and configurable options. This saves those inputs. Also,
         * this method is generic enough that on the settings page
         * we can just call it for persisting any updates after
         * installation.
         * @return $this
         */
        public function saveConfigsFromInstallScreen(){
            // Way easier to explicitly save each field than try to do
            // some magic to check if the constant is defined and such.
            $this->configSet(self::CONFIG_EVENT_AUTOGENERATE_PAGES, (int)$_POST[self::CONFIG_EVENT_AUTOGENERATE_PAGES]);
            $this->configSet(self::CONFIG_EVENT_PAGE_PARENT, (int)$_POST[self::CONFIG_EVENT_PAGE_PARENT]);
            $this->configSet(self::CONFIG_DEFAULT_TIMEZONE, $_POST[self::CONFIG_DEFAULT_TIMEZONE]);
            $this->configSet(self::CONFIG_EVENT_PAGE_TYPE, (int)$_POST[self::CONFIG_EVENT_PAGE_TYPE]);

            return $this;
        }


        /**
         * @todo: table ALTER statements cause upgrades not to return properly; figure
         * out better way of testing if a) the alter statement needs to occur and b) catching
         * exceptions.
         */
        private function installAndUpdate(){
            $this->setupBlocks()
                 ->setupSinglePages()
                 ->setupAttributeCategories()
                 ->setupPermissions();

            /** @var $connection \PDO :: Setup foreign key associations */
            try {
                $connection = Database::connection(Database::getDefaultConnection())->getWrappedConnection();
                $connection->query("ALTER TABLE SchedulizerEvent ADD CONSTRAINT FK_calendar FOREIGN KEY (calendarID) REFERENCES SchedulizerCalendar(id) ON UPDATE CASCADE ON DELETE CASCADE");
                $connection->query("ALTER TABLE SchedulizerEventVersion ADD CONSTRAINT FK_event FOREIGN KEY (eventID) REFERENCES SchedulizerEvent(id) ON DELETE CASCADE");
                $connection->query("ALTER TABLE SchedulizerEventTime ADD CONSTRAINT FK_event2 FOREIGN KEY (eventID) REFERENCES SchedulizerEvent(id) ON UPDATE CASCADE ON DELETE CASCADE");
                $connection->query("ALTER TABLE SchedulizerEventTimeWeekdays ADD CONSTRAINT FK_eventTime FOREIGN KEY (eventTimeID) REFERENCES SchedulizerEventTime(id) ON UPDATE CASCADE ON DELETE CASCADE");
                $connection->query("ALTER TABLE SchedulizerEventTimeNullify ADD CONSTRAINT FK_eventTime2 FOREIGN KEY (eventTimeID) REFERENCES SchedulizerEventTime(id) ON UPDATE CASCADE ON DELETE CASCADE");
                // Tag associations
                $connection->query("ALTER TABLE SchedulizerTaggedEvents ADD CONSTRAINT FK_taggedEvent FOREIGN KEY (eventID) REFERENCES SchedulizerEvent(id) ON DELETE CASCADE");
                $connection->query("ALTER TABLE SchedulizerTaggedEvents ADD CONSTRAINT FK_taggedEvent2 FOREIGN KEY (eventTagID) REFERENCES SchedulizerEventTag(id) ON DELETE CASCADE");
            }catch(\Exception $e){ /** @todo: log out */ }
        }


        /**
         * @return Controller
         */
        private function setupBlocks(){
            if(!is_object(BlockType::getByHandle('schedulizer'))) {
                BlockType::installBlockTypeFromPackage('schedulizer', $this->packageObject());
            }

            if(!is_object(BlockType::getByHandle('schedulizer_event'))) {
                BlockType::installBlockTypeFromPackage('schedulizer_event', $this->packageObject());
            }
            return $this;
        }


        /**
         * @return Controller
         */
        private function setupSinglePages(){
            // Dashboard pages
            SinglePage::add('/dashboard/schedulizer/', $this->packageObject());
            SinglePage::add('/dashboard/schedulizer/calendars', $this->packageObject());
            SinglePage::add('/dashboard/schedulizer/attributes', $this->packageObject());
            SinglePage::add('/dashboard/schedulizer/permissions', $this->packageObject());
            SinglePage::add('/dashboard/schedulizer/settings', $this->packageObject());
            // Hidden
            $spManage = SinglePage::add('/dashboard/schedulizer/calendars/manage', $this->packageObject());
            if( is_object($spManage) ){
                $spManage->setAttribute('exclude_nav', 1);
            }

            return $this;
        }


        /**
         * @return $this
         */
        private function setupAttributeCategories(){
            if( ! AttributeKeyCategory::getByHandle('schedulizer_event') ){
                $attrKeyCat = AttributeKeyCategory::add('schedulizer_event', AttributeKeyCategory::ASET_ALLOW_MULTIPLE, $this->packageObject());
                $attrKeyCat->associateAttributeKeyType( $this->attributeType('text') );
                $attrKeyCat->associateAttributeKeyType( $this->attributeType('boolean') );
                $attrKeyCat->associateAttributeKeyType( $this->attributeType('number') );
                $attrKeyCat->associateAttributeKeyType( $this->attributeType('select') );
                $attrKeyCat->associateAttributeKeyType( $this->attributeType('textarea') );
                $attrKeyCat->associateAttributeKeyType( $this->attributeType('image_file') );
            }

            return $this;
        }

        /**
         * @return $this
         */
        private function setupPermissions(){
            // Calendar permissions: first, we need to create a new permission entity type for "calendar owner"!
            if( ! PermissionAccessEntityType::getByHandle('calendar_owner') ){
                PermissionAccessEntityType::add('calendar_owner', 'Calendar Owner', $this->packageObject());
            }

            // These would be "task" permissions, NOT related to specific entities.
            // The fucking PermissionKeyCategory class's getByHandle() implementation attempts
            // to create an inline cache by storing results in a static class property in the category
            // class, which means we can't rely on getByHandle() to accurately tell us whether
            // it exists or not as a check. So execute a database query to tell reliably.

            //if( ! PermissionKeyCategory::getByHandle('schedulizer') ){
            if( empty(Loader::db()->GetOne("SELECT pkCategoryID FROM PermissionKeyCategories WHERE pkCategoryHandle = 'schedulizer'")) ){
                /** @var $permKeyCategory PermissionCategory */
                $permKeyCategory = PermissionKeyCategory::add('schedulizer', $this->packageObject());
                // Associate access entity types
                foreach(array('group', 'user', 'group_set', 'group_combination', 'calendar_owner') AS $paetHandle){
                    if( $paet = PermissionAccessEntityType::getByHandle($paetHandle) ){
                        $permKeyCategory->associateAccessEntityType($paet);
                    }
                }
            }

            // Setup keys
            foreach(array(
                    'create_tag'    => array(
                        'name'      => t('Create Tags'),
                        'descr'     => t('Is Allowed To Create New Tags')
                    ),
                    'create_calendar' => array(
                        'name'      => t('Add Calendars'),
                        'descr'     => t('Is Allowed To Create New Calendars')
                    ),
                    'manage_calendar_permissions' => array(
                        'name'      => t('Manage Calendar Permissions'),
                        'descr'     => t('Can Manage Calendar Permissions')
                    )
            ) AS $keyHandle => $keyData){
                if( ! SchedulizerPermissionKey::getByHandle($keyHandle) ){
                    SchedulizerPermissionKey::add('schedulizer', $keyHandle, $keyData['name'], $keyData['descr'], 1, 0, $this->packageObject());
                }
            }

            // Calendar entity-specific permissions
            //if( ! PermissionKeyCategory::getByHandle('schedulizer_calendar') ){
            if( empty(Loader::db()->GetOne("SELECT pkCategoryID FROM PermissionKeyCategories WHERE pkCategoryHandle = 'schedulizer_calendar'")) ){
                $schedCalPermKeyCategory = PermissionKeyCategory::add('schedulizer_calendar', $this->packageObject());
                foreach(array('group', 'user', 'group_set', 'group_combination', 'calendar_owner') AS $paetHandle){
                    if( $paetObj = PermissionAccessEntityType::getByHandle($paetHandle) ){
                        $schedCalPermKeyCategory->associateAccessEntityType($paetObj);
                    }
                }
            }

            foreach(array(
                'add_events'    => array(
                    'name'      => t('Add Events'),
                    'descr'     => t('Can Add Events To The Calendar')
                ),
                'delete_events' => array(
                    'name'      => t('Delete Events'),
                    'descr'     => t('Can Delete Events From Calendar')
                )
            ) AS $keyHandle => $keyData){
                if( ! SchedulizerCalendarPermissionKey::getByHandle($keyHandle) ){
                    SchedulizerCalendarPermissionKey::add('schedulizer_calendar', $keyHandle, $keyData['name'], $keyData['descr'], 1, 0, $this->packageObject());
                }
            }

            return $this;
        }

        /**
         * Pass in a key and get the namespace-prepended string;
         * eg. "my_key" becomes "schedulizer.my_key"
         * @param $key
         * @return string
         */
        public static function configKey( $key ){
            return sprintf('%s.%s', self::PACKAGE_HANDLE, $key);
        }

        /**
         * Set a config value; using this method instead of the config
         * object directly lets us swap out the default config store
         * returned by configObj().
         * @param $key string
         * @return mixed
         */
        public function configGet( $key ){
            return $this->configObj()->get(self::configKey($key), self::$configDefaults[$key]);
        }

        /**
         * Set a config value.
         * @param $key string
         * @param $value mixed
         * @return bool
         */
        public function configSet( $key, $value ){
            return $this->configObj()->save(self::configKey($key), $value);
        }


        /**
         * @return \Concrete\Core\Config\Repository\Liaison
         */
        private function configObj(){
            if( $this->_packageConfigObj === null ){
                $this->_packageConfigObj = $this->packageObject()->getConfig();
            }
            return $this->_packageConfigObj;
        }


        /**
         * Get the package object; if it hasn't been instantiated yet, load it.
         * @return \Concrete\Core\Package\Package
         */
        private function packageObject(){
            if( $this->_packageObj === null ){
                //if( ! $this->isPackageInstalled() )
                $this->_packageObj = $this;
                //$this->_packageObj = Package::getByHandle( $this->pkgHandle );
            }
            return $this->_packageObj;
        }


        /**
         * @return AttributeType
         */
        private function attributeType( $handle ){
            if( is_null($this->{"at_{$handle}"}) ){
                $attributeType = AttributeType::getByHandle($handle);
                if( is_object($attributeType) ){
                    $this->{"at_{$handle}"} = $attributeType;
                }
            }
            return $this->{"at_{$handle}"};
        }

    }

}