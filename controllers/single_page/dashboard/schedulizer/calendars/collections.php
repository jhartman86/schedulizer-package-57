<?php namespace Concrete\Package\Schedulizer\Controller\SinglePage\Dashboard\Schedulizer\Calendars {

    use Package;
    use Permissions;
    use \Concrete\Package\Schedulizer\Src\Collection;
    use \Concrete\Package\Schedulizer\Controller\DashboardController;

    class Collections extends DashboardController {

        public function view(){
            $packageObj = Package::getByHandle('schedulizer');
            // If multiple collections are not enabled (the default), go directly to master
            if( ! ((bool) $packageObj->configGet($packageObj::CONFIG_ENABLE_MULTI_COLLECTIONS)) ){
                $masterCollID  = (int) $packageObj->configGet($packageObj::CONFIG_MASTER_COLLECTION_ID);
                // Have to make sure it exists if user had been swapping enabled/disabled master collection
                $collectionObj = Collection::getByID($masterCollID);
                if( is_object($collectionObj) ){
                    $this->redirect('/dashboard/schedulizer/calendars/collections/manage', $masterCollID);
                    return;
                }
            }

            $this->hideDefaultC5DashboardHeader();
            $this->forceFullHeight();
            $this->set('collections', Collection::fetchAll());
        }

    }

}