<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use Permissions;
    use \Concrete\Package\Schedulizer\Src\EventCategory;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;
    use \Symfony\Component\HttpFoundation\Response;

    class EventCategoriesResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        protected function httpGet( $id ){
            $this->setResponseData(EventCategory::fetchAll());
        }

        /**
         * Create a new category
         * @todo: permissions, pass user (api key determines?), and timezone options
         */
        protected function httpPost(){
            $this->checkSettingsPagePermission();
            $data = $this->scrubbedPostData();
            $categoryObj = EventCategory::createOrGetExisting($data);
            $this->setResponseData($categoryObj);
            $this->setResponseCode(Response::HTTP_CREATED);
        }

        /**
         * @param $id
         */
        protected function httpPut( $id ){
            $this->checkSettingsPagePermission();
            $categoryObj = EventCategory::getByID($id);
            $categoryObj->update($this->scrubbedPostData());
            $this->setResponseData($categoryObj);
            $this->setResponseCode(Response::HTTP_ACCEPTED);
        }

        /**
         * @param $id
         * @throws ApiException
         */
        protected function httpDelete( $id ){
            $this->checkSettingsPagePermission();
            $categoryObj = EventCategory::getByID($id);
            if( ! is_object($categoryObj) ){
                throw ApiException::generic("Category already gone.");
            }
            $categoryObj->delete();
            $this->setResponseCode(Response::HTTP_NO_CONTENT);
        }

        /**
         * Since we don't have a task permission for creating categories, we just
         * use permissions based on visibility of the settings page.
         * @throws ApiException
         */
        protected function checkSettingsPagePermission(){
            $pageObj     = \Concrete\Core\Page\Page::getByPath('/dashboard/schedulizer/settings');
            $permissions = new Permissions($pageObj);

            if( ! $permissions->canViewPage() ){
                throw ApiException::permissionInvalid('no permiz');
            }
        }

    }

}