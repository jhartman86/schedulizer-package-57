<?php namespace Concrete\Package\Schedulizer\Controller\SinglePage\Dashboard\Schedulizer {

    use Package;
    use Loader;
    use \Concrete\Package\Schedulizer\Controller\DashboardController;

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