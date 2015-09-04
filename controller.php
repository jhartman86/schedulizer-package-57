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
    use Group;
    use PermissionKeyCategory; /** @see \Concrete\Core\Permission\Category */
    use \Concrete\Core\Attribute\Key\Category AS AttributeKeyCategory;
    use \Concrete\Core\Attribute\Type AS AttributeType;
    use \Concrete\Package\Schedulizer\Src\Api\ApiOnStart;
    use \Concrete\Core\Permission\Access\Entity\Type AS PermissionAccessEntityType;
    use \Concrete\Core\Permission\Access\Access AS PermissionAccess;
    use \Concrete\Core\Permission\Access\Entity\GroupEntity as GroupPermissionAccessEntity;
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
              CONFIG_EVENT_PAGE_TYPE            = 'event_page_type',
              CONFIG_ENABLE_MASTER_COLLECTION   = 'enable_master_collection',
              CONFIG_MASTER_COLLECTION_ID       = 'master_collection_id';

        protected static $configDefaults        = array(
            self::CONFIG_DEFAULT_TIMEZONE           => 'UTC',
            self::CONFIG_EVENT_AUTOGENERATE_PAGES   => 0,
            self::CONFIG_EVENT_PAGE_PARENT          => null,
            self::CONFIG_EVENT_PAGE_TYPE            => null,
            self::CONFIG_ENABLE_MASTER_COLLECTION   => 1,
            self::CONFIG_MASTER_COLLECTION_ID       => null
        );

        protected $pkgHandle                = self::PACKAGE_HANDLE;
        protected $appVersionRequired       = '5.7.3.2';
        protected $pkgVersion               = '1.12';

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

            // @note: when C5 support permission loading order correctly (as in, packages have the
            // on_start method called PRIOR to sessions trying to initialize serialized objects),
            // we should move the include and bind calls found in application/config/app.php to
            // here

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
                $apiOnStart->addRoute('collection', 'CollectionResource');
                // GET,POST,PUT,DELETE
                $apiOnStart->addRoute('collection_event', 'CollectionEventResource');
                // GET
                $apiOnStart->addRoute('calendar_list', 'CalendarListResource');
                // GET,POST,PUT,DELETE
                $apiOnStart->addRoute('event', 'EventResource');
                // GET,POST,DELETE
                $apiOnStart->addRoute('event_time_nullify', 'EventTimeNullifyResource');
                // GET
                $apiOnStart->addRoute('event_tags', 'EventTagsResource');
                // GET,PUT,DELETE
                $apiOnStart->addRoute('event_categories', 'EventCategoriesResource');
                // GET
                $apiOnStart->addRoute('event_list', 'EventListResource');
                // GET
                $apiOnStart->addRoute('timezones', 'TimezoneResource');
            });

            return $this;
        }


        /**
         *  Note: C5's router fails to implement the full Symfony routing options
         * (hence why we customize the API stuff above), so to pass an optional parameter
         * we have to register the route twice :(
         */
        protected function setupC5Routes(){
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


        /**
         * @todo: uninstall all associated permission entities?
         */
        public function uninstall(){
            parent::uninstall();

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
                'SchedulizerEventSearchIndexAttributes',
                'SchedulizerCalendarPermissionAssignments',
                'SchedulizerCategorizedEvents',
                'SchedulizerEventCategory',
                'SchedulizerCollection',
                'SchedulizerCollectionCalendars',
                'SchedulizerCollectionEvents'
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
                $this->installAndUpdate( false );
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
            if( !class_exists("\\Concrete\\Package\\Schedulizer\\Src\\Install\\Support") ) {
                include DIR_PACKAGES . '/' . self::PACKAGE_HANDLE . '/src/Install/Support.php';
            }
            if( Src\Install\Support::meetsRequirements() ){
                // the on_start method doesn't run during installation, so we need to
                // call setupClassBindings here or it'll fail or only partially install
                $this->setupClassBindings();
                $this->_packageObj = parent::install();
                $this->saveConfigsFromInstallScreen()
                     ->installAndUpdate( true );
                return;
            }
            throw new Exception("System requirements not met.");
        }


        /**
         * With 5.7.4.1, the use of Doctrine's annotation parser causes major issues
         * EVEN THOUGH FUCKING DOCTRINE ISN'T BEING USED. So override the parent methods
         * and remove calls to annoation parser.
         */
        public function installDatabase(){
            if (file_exists($this->getPackagePath() . '/' . FILENAME_PACKAGE_DB)) {
                // Legacy db.xml
                parent::installDB($this->getPackagePath() . '/' . FILENAME_PACKAGE_DB);
            }
        }


        /**
         * More doctrine stuff we have to override...
         * @throws \Exception
         */
        public function upgradeDatabase(){
            if (file_exists($this->getPackagePath() . '/' . FILENAME_PACKAGE_DB)) {
                // Legacy db.xml
                // currently this is just done from xml
                $db = Database::get();
                $db->beginTransaction();

                $parser = \Concrete\Core\Database\Schema\Schema::getSchemaParser(simplexml_load_file($this->getPackagePath() . '/' . FILENAME_PACKAGE_DB));
                $parser->setIgnoreExistingTables(false);
                $toSchema = $parser->parse($db);

                $fromSchema = $db->getSchemaManager()->createSchema();
                $comparator = new \Doctrine\DBAL\Schema\Comparator();
                $schemaDiff = $comparator->compare($fromSchema, $toSchema);
                $saveQueries = $schemaDiff->toSaveSql($db->getDatabasePlatform());

                foreach ($saveQueries as $query) {
                    $db->query($query);
                }

                $db->commit();
            }
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
            $this->configSet(self::CONFIG_ENABLE_MASTER_COLLECTION, (int)$_POST[self::CONFIG_ENABLE_MASTER_COLLECTION]);
            $this->configSet(self::CONFIG_MASTER_COLLECTION_ID, (int)$_POST[self::CONFIG_MASTER_COLLECTION_ID]);

            return $this;
        }


        /**
         * Run all update methods.
         */
        private function installAndUpdate( $isInstall = false ){
            $this->tryVersionSpecificUpdates()
                 ->setupDatabaseExtra()
                 ->setupBlocks()
                 ->setupSinglePages()
                 ->setupAttributeCategories()
                 ->setupPermissions()
                 ->setupThumbnailTypes();

            // Only run on install
            if( $isInstall ){
                $this->setupPermissionAccessEntities()
                     ->setupMasterCollectionIfEnabled();
            }
        }


        /**
         * Sometimes we require version-specific updates. This takes care of.
         * @return $this
         */
        private function tryVersionSpecificUpdates(){
            // Setup autoloading for the version_updates directory
            $symfonyLoader = new \Concrete\Core\Foundation\ModifiedPsr4ClassLoader();
            $symfonyLoader->addPrefix('Concrete\\Package\\Schedulizer\\VersionUpdates', DIR_PACKAGES . '/schedulizer/version_updates');
            $symfonyLoader->register();

            // Re-fetch the package to get the version we're installing up to
            // as $this return the correct value
            $targetVersion = self::getByHandle(self::PACKAGE_HANDLE)->getPackageVersion();
            $klassName     = sprintf("V%s", preg_replace('/[^0-9]/', '', $targetVersion));
            $nsKlassName   = sprintf("%s\\VersionUpdates\\%s", __NAMESPACE__, $klassName);
            if( class_exists($nsKlassName) ){
                $updater = new $nsKlassName;
                $updater->run();
            }

            return $this;
        }


        /**
         * Handles foreign key setup since not supported w/ db.xml.
         * @note: updates are logged in case of errors
         * @todo: what if user doesn't have MySQL permissions to access the information_schema table?
         */
        private function setupDatabaseExtra(){
            $groupLogger = new \Concrete\Core\Logging\GroupLogger(false, \Monolog\Logger::INFO);
            $groupLogger->write("Monitoring DB upgrade for Schedulizer");

            /** @var $connection \PDO :: Setup foreign key associations */
            try {
                $connection = Database::connection(Database::getDefaultConnection())->getWrappedConnection();
                $existing   = $connection->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_TYPE = 'FOREIGN KEY'");
                $existing->execute();
                // = array of existing foreign key names already configured
                $existingConstraints = $existing->fetchAll(\PDO::FETCH_COLUMN);
                $groupLogger->write(sprintf("Existing constraints (will be skipped): \n%s", join(",\n", $existingConstraints)));

                // @todo: implement tying to versions
                $constraints = array(
                    'eventCalendarID' => array(
                        'table' => 'SchedulizerEvent', 'fkCol' => 'calendarID', 'fkRefs' => 'SchedulizerCalendar(id)', 'cascades' => array('update', 'delete')
                    ),
                    'eventVersionEventID' => array(
                        'table' => 'SchedulizerEventVersion', 'fkCol' => 'eventID', 'fkRefs' => 'SchedulizerEvent(id)', 'cascades' => array('delete')
                    ),
                    'eventTimeEventID' => array(
                        'table' => 'SchedulizerEventTime', 'fkCol' => 'eventID', 'fkRefs' => 'SchedulizerEvent(id)', 'cascades' => array('update', 'delete')
                    ),
                    'eventTimeWeekdaysEventTimeID' => array(
                        'table' => 'SchedulizerEventTimeWeekdays', 'fkCol' => 'eventTimeID', 'fkRefs' => 'SchedulizerEventTime(id)', 'cascades' => array('update', 'delete')
                    ),
                    'eventTimeNullifyEventTimeID' => array(
                        'table' => 'SchedulizerEventTimeNullify', 'fkCol' => 'eventTimeID', 'fkRefs' => 'SchedulizerEventTime(id)', 'cascades' => array('update', 'delete')
                    ),
                    'eventTaggedEventID' => array(
                        'table' => 'SchedulizerTaggedEvents', 'fkCol' => 'eventID', 'fkRefs' => 'SchedulizerEvent(id)', 'cascades' => array('delete')
                    ),
                    'eventTaggedTagID' => array(
                        'table' => 'SchedulizerTaggedEvents', 'fkCol' => 'eventTagID', 'fkRefs' => 'SchedulizerEventTag(id)', 'cascades' => array('delete')
                    ),
                    'eventCategorizedEventID' => array(
                        'table' => 'SchedulizerCategorizedEvents', 'fkCol' => 'eventID', 'fkRefs' => 'SchedulizerEvent(id)', 'cascades' => array('delete')
                    ),
                    'eventCategorizedCategoryID' => array(
                        'table' => 'SchedulizerCategorizedEvents', 'fkCol' => 'eventCategoryID', 'fkRefs' => 'SchedulizerEventCategory(id)', 'cascades' => array('delete')
                    ),
                    'collectionCalendarCollectionID' => array(
                        'table' => 'SchedulizerCollectionCalendars', 'fkCol' => 'collectionID', 'fkRefs' => 'SchedulizerCollection(id)', 'cascades' => array('delete')
                    ),
                    'collectionEventCollectionID' => array(
                        'table' => 'SchedulizerCollectionEvents', 'fkCol' => 'collectionID', 'fkRefs' => 'SchedulizerCollection(id)', 'cascades' => array('delete')
                    ),
                    'collectionEventEventID' => array(
                        'table' => 'SchedulizerCollectionEvents', 'fkCol' => 'eventID', 'fkRefs' => 'SchedulizerEvent(id)', 'cascades' => array('delete')
                    )
                );

                foreach($constraints AS $constrName => $def){
                    try {
                        if( !in_array($constrName, $existingConstraints) ){
                            $query = "ALTER TABLE {$def['table']}
                            ADD CONSTRAINT {$constrName}
                            FOREIGN KEY ({$def['fkCol']})
                            REFERENCES {$def['fkRefs']}";
                            $query .= in_array('update', $def['cascades']) ? ' ON UPDATE CASCADE' : '';
                            $query .= in_array('delete', $def['cascades']) ? ' ON DELETE CASCADE' : '';
                            $connection->exec($query);
                            $groupLogger->write(sprintf("Successfully added contraint: %s", $constrName));
                        }
                    }catch(\Exception $e){
                        $groupLogger->write(sprintf("Failed with constraint: %s \n Exception: %s", $constrName, $e->getMessage()));
                    }
                }
            }catch(\Exception $e){
                $groupLogger->write(sprintf("Updating constraints failed, caught outer exception: \n %s", $e->getMessage()));
            }

            $groupLogger->close();

            return $this;
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
            SinglePage::add('/dashboard/schedulizer/calendars/search', $this->packageObject());
            SinglePage::add('/dashboard/schedulizer/calendars/collections', $this->packageObject());
            SinglePage::add('/dashboard/schedulizer/attributes', $this->packageObject());
            SinglePage::add('/dashboard/schedulizer/permissions', $this->packageObject());
            SinglePage::add('/dashboard/schedulizer/settings', $this->packageObject());
            // Hidden
            $spManage = SinglePage::add('/dashboard/schedulizer/calendars/manage', $this->packageObject());
            if( is_object($spManage) ){
                $spManage->setAttribute('exclude_nav', 1);
            }
            $spManageCollections = SinglePage::add('/dashboard/schedulizer/calendars/collections/manage', $this->packageObject());
            if( is_object($spManageCollections) ){
                $spManageCollections->setAttribute('exclude_nav', 1);
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
         * @todo: setup task permissions (create tags, add/delete cals, manage cal permissions) with
         * defaults to Admin and Calendar owner (where relevant), but only on INSTALL
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
                'edit_calendar'   => array(
                    'name'      => t('Edit Calendars'),
                    'descr'     => t('Is Allowed To Edit Calendars')
                ),
                'delete_calendar' => array(
                    'name'      => t('Delete Calendars'),
                    'descr'     => t('Is Allowed To Delete Calendars')
                ),
                'manage_calendar_permissions' => array(
                    'name'      => t('Manage Calendar Permissions'),
                    'descr'     => t('Can Manage Calendar Permissions')
                )
            ) AS $keyHandle => $keyData){
                if( ! SchedulizerPermissionKey::getByHandle($keyHandle) ){
                    SchedulizerPermissionKey::add('schedulizer', $keyHandle, $keyData['name'], $keyData['descr'], 0, 0, $this->packageObject());
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
                'edit_events'   => array(
                    'name'      => t('Edit Events'),
                    'descr'     => t('Can Add and Update Calendar Events')
                ),
                'delete_events' => array(
                    'name'      => t('Delete Events'),
                    'descr'     => t('Can Delete Events From Calendar')
                )
            ) AS $keyHandle => $keyData){
                if( ! SchedulizerCalendarPermissionKey::getByHandle($keyHandle) ){
                    SchedulizerCalendarPermissionKey::add('schedulizer_calendar', $keyHandle, $keyData['name'], $keyData['descr'], 0, 0, $this->packageObject());
                }
            }

            return $this;
        }

        /**
         * Setup event thumbnail image type.
         * @return $this
         */
        private function setupThumbnailTypes(){
            $eventThumbnail = \Concrete\Core\File\Image\Thumbnail\Type\Type::getByHandle('event_thumb');
            if( ! is_object($eventThumbnail) ){
                $type = new \Concrete\Core\File\Image\Thumbnail\Type\Type();
                $type->setName('Event Thumb');
                $type->setHandle('event_thumb');
                $type->setWidth(740);
                $type->save();
            }

            return $this;
        }

        /**
         * Only executed on first install; this sets up the defaults for "task" permissions.
         * @return $this
         */
        private function setupPermissionAccessEntities(){
            foreach(array('create_tag', 'create_calendar', 'edit_calendar', 'delete_calendar', 'manage_calendar_permissions') AS $permKeyHandle){
                $pkObj = SchedulizerPermissionKey::getByHandle($permKeyHandle);
                $paObj = $pkObj->getPermissionAccessObject();

                // Standard, assign admin
                if( !is_object($paObj) ){
                    // Permission Access Record
                    $paObj = PermissionAccess::create($pkObj);
                    // Permissionable Access Entity
                    $peAdmins = GroupPermissionAccessEntity::getOrCreate(Group::getByID(ADMIN_GROUP_ID));
                    // Add $peAdmins Entity to $paObj
                    $paObj->addListItem($peAdmins);
                    // Save Assignment
                    $pkObj->getPermissionAssignmentObject()->assignPermissionAccess($paObj);
                }

                // Since this is once off assigning the calendar owner, we'll do it in the loop
                if( $permKeyHandle === 'edit_calendar' ){
                    // We already know the $paObj (Access Record) exists
                    $peCalendarOwner = \Concrete\Package\Schedulizer\Src\Permission\Access\Entity\CalendarOwnerEntity::getOrCreate();
                    $paObj->addListItem($peCalendarOwner);
                    $pkObj->getPermissionAssignmentObject()->assignPermissionAccess($paObj);
                }
            }

            return $this;
        }

        /**
         * During install, the default is to enable a master collection. In which case we
         * create a collection automatically, and save it as the master collectionID.
         * @return $this
         */
        private function setupMasterCollectionIfEnabled(){
            if( (bool) $this->configGet(self::CONFIG_ENABLE_MASTER_COLLECTION) ){
                $collectionObj = \Concrete\Package\Schedulizer\Src\Collection::create((object) array(
                    'title'     => 'Approvals',
                    'ownerID'   => 1, // @todo: might fail on auto-incr systems other than +1
                    'collectionCalendars' => array()
                ));

                $this->configSet(self::CONFIG_MASTER_COLLECTION_ID, $collectionObj->getID());
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
