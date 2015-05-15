<?php namespace Concrete\Package\Schedulizer\Controller\SinglePage\Dashboard\Schedulizer {

    use Package;
    use Loader;
    use \Concrete\Package\Schedulizer\Controller\DashboardController;

    /**
     * Class Settings
     * @note: even though in the page view you can edit tags and categories, those are
     * handled via angular and the API.
     * @package Concrete\Package\Schedulizer\Controller\SinglePage\Dashboard\Schedulizer
     */
    class Settings extends DashboardController {

        /**
         * @todo: token validation - can the user adjust/save settings?
         */
        public function save(){
            /** @var $packageObj \Concrete\Package\Schedulizer\Controller */
            $packageObj = Package::getByHandle('schedulizer');
            $packageObj->saveConfigsFromInstallScreen();
            $this->redirect('/dashboard/schedulizer/settings', 'saved');
        }

        public function saved(){
            $this->set('message', t('Settings saved.'));
        }

    }

}