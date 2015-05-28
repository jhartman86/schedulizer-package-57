<?php namespace Concrete\Package\Schedulizer\Controller\SinglePage\Dashboard\Schedulizer\Calendars {

    use Config;
    use Permissions;
    use \Concrete\Package\Schedulizer\Controller\DashboardController;

    class Search extends DashboardController {

        public function view(){
            $this->hideDefaultC5DashboardHeader();
            $this->forceFullHeight();
        }

    }

}