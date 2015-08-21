<?php namespace Concrete\Package\Schedulizer\Controller\SinglePage\Dashboard\Schedulizer\Calendars {

    use Config;
    use Permissions;
    use \Concrete\Package\Schedulizer\Src\Collection;
    use \Concrete\Package\Schedulizer\Controller\DashboardController;

    class Collections extends DashboardController {

        public function view(){
            $this->hideDefaultC5DashboardHeader();
            $this->forceFullHeight();
            $this->set('collections', Collection::fetchAll());
        }

    }

}