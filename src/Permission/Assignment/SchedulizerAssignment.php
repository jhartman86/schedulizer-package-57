<?php namespace Concrete\Package\Schedulizer\Src\Permission\Assignment {

    use Loader;
    use Router;
    use \Concrete\Core\Permission\Assignment\Assignment;

    class SchedulizerAssignment extends Assignment {

        const PERMISSION_CATEGORY_HANDLE = 'schedulizer';

        /**
         * Override the parent method as it still uses tools files, which no
         * longer route correctly via packages.
         * @param bool $task
         * @return mixed
         */
        public function getPermissionKeyToolsURL( $task = false ){
            if( ! $task ){
                $task = 'save_permission';
            }
            $query = http_build_query(array(
                'task'      => $task,
                'pkID'      => $this->pk->getPermissionKeyID()
            )) . sprintf("&%s", Loader::helper('validation/token')->getParameter($task));
            return Router::route(array(sprintf('permission/category/schedulizer?%s',$query), 'schedulizer'));
        }

    }

}