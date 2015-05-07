<?php namespace Concrete\Package\Schedulizer\Src\Api {

    use \Symfony\Component\HttpFoundation\Response;

    class ApiException extends \Exception {

        /**
         * Should never be new'd directly, but instead instantiated via
         * the static methods below.
         * @param string $message
         * @param int $code
         * @param Exception $previous
         */
        public function __construct($message = "", $code = Response::HTTP_NOT_ACCEPTABLE, Exception $previous = null){
            parent::__construct($message, $code, $previous);
        }

        public static function generic( $message = 'Invalid API call.' ){
            return new self($message);
        }

        public static function invalidRoute( $message = 'Invalid route.' ){
            return new self($message);
        }

        public static function notFound( $message = 'Resource not found.' ){
            return new self($message, Response::HTTP_NOT_FOUND);
        }

        public static function httpMethodNotSupported( $message = 'No match for HTTP method.' ){
            return new self($message, Response::HTTP_NOT_IMPLEMENTED);
        }

        public static function dependentResourceInvalid( $message = 'Request depends on a resource that is invalid.' ){
            return new self($message, Response::HTTP_FAILED_DEPENDENCY);
        }

        public static function permissionInvalid( $message = 'Insufficient permission level.' ){
            return new self($message, Response::HTTP_UNAUTHORIZED);
        }

    }

}