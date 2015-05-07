<?php namespace Concrete\Package\Schedulizer\Controller\SinglePage\Dashboard\Schedulizer {

    use Config;
    use Permissions;
    use \Concrete\Package\Schedulizer\Controller\DashboardController;
    use \Concrete\Package\Schedulizer\Src\Calendar;
    use \Concrete\Package\Schedulizer\Src\Bin\TimeConversion;

    class Calendars extends DashboardController {

        public function on_start(){
            parent::on_start();
            $this->set('permissionsObj', new Permissions());
        }

        public function view(){
            $this->hideDefaultC5DashboardHeader();
            $this->forceFullHeight();
            $this->set('calendars', Calendar::fetchAll());
            $this->set('conversionHelper', new TimeConversion());
        }

    }

}