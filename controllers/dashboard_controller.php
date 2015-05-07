<?php namespace Concrete\Package\Schedulizer\Controller {

    use View;
    use User;
    use Loader;
    use \Concrete\Core\Routing\URL;

    class DashboardController extends \Concrete\Core\Page\Controller\DashboardPageController {

        protected $_userObj;

        public function on_start(){
            parent::on_start();
            $this->requireAsset('redactor');
            $this->requireAsset('core/file-manager');
            $this->addHeaderItem( \Core::make('helper/html')->css('app.css', 'schedulizer') );
            $this->addHeaderItem('<script type="text/javascript">var __schedulizer = {dashboard:"'.View::url('/dashboard/schedulizer').'",api:"'.View::url('/_schedulizer').'",ajax:"'.URL::route(array('','schedulizer')).'"};</script>');
            $this->addFooterItem( \Core::make('helper/html')->javascript('core.js', 'schedulizer') );
            $this->addFooterItem( \Core::make('helper/html')->javascript('app.js', 'schedulizer') );
            $this->addFooterItem('<script type="text/javascript">var CCM_EDITOR_SECURITY_TOKEN = \''.Loader::helper('validation/token')->generate('editor').'\'</script>');
        }

        /**
         * @return User
         */
        protected function currentUser(){
            if( $this->_userObj === null ){
                $this->_userObj = new User;
            }
            return $this->_userObj;
        }

        /**
         * C5's default way of styling the gray page headers on dashboard pages
         * doesn't make much sense, this lets us use our own.
         */
        public function hideDefaultC5DashboardHeader(){
            $this->addHeaderItem('<style type="text/css">#ccm-dashboard-content.container-fluid > header {display:none !important;}</style>');
        }

        /**
         * Force the height to 100% in the dasboard.
         */
        public function forceFullHeight(){
            $this->addHeaderItem('<style type="text/css">html,#ccm-dashboard-page.ccm-ui,#ccm-dashboard-page.ccm-ui #ccm-dashboard-content.container-fluid,.schedulizer-app,#ccm-dashboard-page.ccm-ui #ccm-dashboard-content.container-fluid .ccm-dashboard-content-full,.schedulizer-app .calendar-wrap,.calendry-instance {height:100%;padding-bottom:0;}.schedulizer-app .app-wrap {height:calc(100% - 66px);}body {height:calc(100% - 49px);}</style>');
        }

    }

}