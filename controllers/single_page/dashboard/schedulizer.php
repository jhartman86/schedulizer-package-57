<? namespace Concrete\Package\Schedulizer\Controller\SinglePage\Dashboard {

    use \Concrete\Package\Schedulizer\Controller\DashboardController;

    class Schedulizer extends DashboardController {

        public function view() {
            $this->redirect('/dashboard/schedulizer/calendars');
        }

    }
}