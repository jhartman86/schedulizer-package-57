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
                    $this->set('isMasterCollection', $this->isMasterCollection($collectionObj));
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
        private function isMasterCollection( \Concrete\Package\Schedulizer\Src\Collection $collectionObj ){
            $packageObj     = Package::getByHandle('schedulizer');
            $masterCollObj  = Collection::getByID((int) $packageObj->configGet($packageObj::CONFIG_MASTER_COLLECTION_ID));
            if( ! is_object($masterCollObj) ){
                return false;
            }
            return (int)$masterCollObj->getID() === (int)$collectionObj->getID();
        }

    }

}