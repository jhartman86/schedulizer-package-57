<?php namespace Concrete\Package\Schedulizer\Src\Api {

    use \Symfony\Component\HttpFoundation\Response;

    class ApiException extends \Exception {

        protected $type;

        /**
         * Should never be new'd directly, but instead instantiated via
         * the static methods below.
         * @param string $message
         * @param int $code
         * @param \Exception $previous
         */
        public function __construct($message = "", $code = Response::HTTP_NOT_ACCEPTABLE, \Exception $previous = null){
            parent::__construct($message, $code, $previous);
        }

        public function getType(){
            return $this->type;
        }

        public static function generic( $message = 'Invalid API call.' ){
            $e = new self($message);
            $e->type = __FUNCTION__;
            return $e;
        }

        public static function validationError( $message = 'Validation Error.' ){
            $e = new self($message, Response::HTTP_BAD_REQUEST);
            $e->type = __FUNCTION__;
            return $e;
        }

        public static function invalidRoute( $message = 'Invalid route.' ){
            $e = new self($message);
            $e->type = __FUNCTION__;
            return $e;
        }

        public static function notFound( $message = 'Resource not found.' ){
            $e = new self($message, Response::HTTP_NOT_FOUND);
            $e->type = __FUNCTION__;
            return $e;
        }

        public static function httpMethodNotSupported( $message = 'No match for HTTP method.' ){
            $e = new self($message, Response::HTTP_NOT_IMPLEMENTED);
            $e->type = __FUNCTION__;
            return $e;
        }

        public static function dependentResourceInvalid( $message = 'Request depends on a resource that is invalid.' ){
            $e = new self($message, Response::HTTP_FAILED_DEPENDENCY);
            $e->type = __FUNCTION__;
            return $e;
        }

        public static function permissionInvalid( $message = 'Insufficient permission level.' ){
            $e = new self($message, Response::HTTP_UNAUTHORIZED);
            $e->type = __FUNCTION__;
            return $e;
        }

    }

}