<?php namespace Concrete\Package\Schedulizer\Src\Api {

    use Config;
    use Gettext\Languages\Exporter\Json;
    use User;
    use \Symfony\Component\HttpFoundation\Request;
    use \Symfony\Component\HttpFoundation\JsonResponse;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;

    /**
     * Class ApiDispatcher
     * @package Concrete\Package\Schedulizer\Src\Api
     */
    abstract class ApiDispatcher {

        /** @var $requestObj \Symfony\Component\HttpFoundation\Request */
        protected $requestObj;
        /** @var $responseObj \Symfony\Component\HttpFoundation\JsonResponse */
        protected $responseObj;
        /** @var $routeParams array */
        protected $routeParams;
        /** @var $_requestParams stdObject|null */
        protected $_requestParams;
        /** @var $_postBody stdObject|array|null */
        protected $_postBody;
        /** @var $_blacklistDefaults array */
        protected $_blacklistDefaults = array('id', 'createdUTC', 'modifiedUTC');
        /** @var $blacklistExtras array */
        protected $blacklistExtras = array();
        /** @var $_scrubbedPostData stdObject|array|null */
        protected $_scrubbedPostBody;
        /** @var $_currentUser User */
        protected $_currentUser;

        /**
         * Since all implementing classes extend ApiDispatcher, we just point all
         * the symfony route settings to the 'dispatch' method and this
         * handles setup.
         * @param Request $request
         * @param null $routeParams
         * @return JsonResponse
         * @throws Exception
         */
        public function dispatch( Request $request, $routeParams = null ){
            $this->requestObj  = $request;
            $this->responseObj = new JsonResponse();
            $this->routeParams = explode('/', $routeParams);
            // Method to call in the implementing class, eg. "httpGet", "httpPost"
            $handlerMethod = 'http' . ucfirst($this->requestObj->getMethod());

            try {
                // Inspects $this (which should only be an instance of a child class
                // extending ApiDispatcher), and if it has a method that matches the
                // HTTP request method (get,put,post,delete), call it
                if( method_exists($this, $handlerMethod) ){
                    call_user_func_array(array($this, $handlerMethod), $this->routeParams);
                }else{
                    throw ApiException::httpMethodNotSupported(sprintf('No match for HTTP method %s at this route.', $this->requestObj->getMethod()));
                }
            }catch( \Exception $e ){
                $this->responseObj->setStatusCode($e->getCode());
                $this->responseObj->setdata((object)array(
                    'error' => $e->getMessage()
                ));
            }

            return $this->responseObj;
        }


        /**
         * Set the data for the response.
         * @param $anything
         * @throws \Exception
         */
        protected function setResponseData( $anything ){
            $this->responseObj->setData($anything);
        }


        /**
         * Set the response code.
         * @param int $code
         */
        protected function setResponseCode( $code = JsonResponse::HTTP_OK ){
            $this->responseObj->setStatusCode($code);
        }


        /**
         * Get a parsed stdObject of the query string.
         * @return stdObject
         */
        protected function requestParams(){
            if( $this->_requestParams === null ){
                parse_str($this->requestObj->getQueryString(), $parsed);
                $this->_requestParams = (object) $parsed;
            }
            return $this->_requestParams;
        }


        /**
         * Parse incoming post body as json.
         * @return array|stdObject|mixed|null
         */
        protected function postData(){
            if( $this->_postBody === null ){
                $this->_postBody = json_decode(file_get_contents('php://input'));
                if( empty($this->_postBody) ){
                    $this->_postBody = new \stdClass();
                }
            }
            return $this->_postBody;
        }


        /**
         * To blacklist items from being parsed into the post data,
         * set $blacklistExtras in the implementing class. By default,
         * id, createdUTC, and modifiedUTC are blacklisted; to have those
         * *not* be blacklisted, override $_blacklistDefaults to an empty
         * array in the implementing class.
         * @return array|stdObject|mixed|null
         */
        protected function scrubbedPostData(){
            if( $this->_scrubbedPostBody === null ){
                $this->_scrubbedPostBody = $this->postData();
                if( is_object($this->_scrubbedPostBody) ){
                    $blacklisted = array_unique(array_merge($this->_blacklistDefaults, $this->blacklistExtras));
                    foreach($blacklisted AS $item){
                        unset($this->_scrubbedPostBody->{$item});
                    }
                }
            }
            return $this->_scrubbedPostBody;
        }


        /**
         * @return User
         */
        protected function currentUser(){
            if( $this->_currentUser === null ){
                $this->_currentUser = new User();
            }
            return $this->_currentUser;
        }

    }

}