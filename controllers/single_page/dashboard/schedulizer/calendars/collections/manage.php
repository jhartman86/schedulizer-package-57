<?php namespace Concrete\Package\Schedulizer\Controller\SinglePage\Dashboard\Schedulizer\Calendars\Collections {

    use Package;
    use Permissions;
    use \Concrete\Package\Schedulizer\Src\Collection;
    use \Concrete\Package\Schedulizer\Controller\DashboardController;

    class Manage extends DashboardController {

        public function view( $collectionID = null ){
            $this->hideDefaultC5DashboardHeader();
            $this->forceFullHeight();

            try {
                $collectionObj = Collection::getByID( $collectionID );
                if( is_object($collectionObj) ){
                    $this->set('collectionObj', $collectionObj);
                    $this->set('pageTitle', $collectionObj->getTitle());
                    $this->set('isMasterCollection', $this->isMasterCollection());
                    return;
                }
                throw new \Exception('No collection object');
            }catch(\Exception $e){
                $this->redirect('/dashboard/schedulizer/calendars/collections');
            }
        }

        /**
         * @return bool
         */
        private function isMasterCollection(){
            $packageObj = Package::getByHandle('schedulizer');
            return (bool) $packageObj->configGet($packageObj::CONFIG_ENABLE_MASTER_COLLECTION);
        }

    }

}