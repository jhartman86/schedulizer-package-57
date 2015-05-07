<?php namespace Concrete\Package\Schedulizer\Src\Api {

    use \Symfony\Component\Routing\Route AS SymfonyRoute;
    use \Symfony\Component\HttpFoundation\Request;
    use \Symfony\Component\HttpKernel\HttpKernel;
    use \Symfony\Component\EventDispatcher\EventDispatcher;
    use \Symfony\Component\HttpKernel\Controller\ControllerResolver;
    use \Symfony\Component\HttpKernel\EventListener\RouterListener;
    use \Symfony\Component\Routing\RouteCollection;
    use \Symfony\Component\Routing\Matcher\UrlMatcher;
    use \Symfony\Component\Routing\RequestContext;

    class ApiOnStart {

        /** @var $routeCollection RouteCollection */
        protected $routeCollection;

        protected function __construct( \Closure $closure ){
            $this->routeCollection = new RouteCollection();
            $closure($this);
            $this->setup();
        }

        /**
         * Just a simple way to exec this class without new'ing it somewhere else.
         * @param callable $closure
         */
        public static function execute( \Closure $closure ){
            new self($closure);
        }


        /**
         * Create a route for a resource with standard options.
         * With $for = 'things', $klass = 'ThingResource', a route
         * will be added at /schedulizer/things/... that will automatically
         * be mapped to appropriate GET,POST,PUT,DELETE methods if they exist.
         * @param $for string eg. 'calendars'
         * @param $klass string eg. 'CalendarResource'
         */
        public function addRoute( $for, $klass ){
            $routePath = sprintf('/_schedulizer/%s/{routeParams}', $for);
            $routeObj  = new SymfonyRoute($routePath, array(
                '_controller' => sprintf('\Concrete\Package\Schedulizer\Src\Api\Resource\%s::dispatch', $klass),
                'routeParams' => null
            ), array('routeParams' => '.*'));
            $this->routeCollection->add(sprintf('schedulizer_%s', $for), $routeObj);
        }


        /**
         * Execute the router.
         */
        protected function setup(){
            try {
                $request    = Request::createFromGlobals();
                $request->enableHttpMethodParameterOverride();
                $matcher    = new UrlMatcher($this->routeCollection, new RequestContext());
                $dispatch   = new EventDispatcher();
                $dispatch->addSubscriber(new RouterListener($matcher));
                $resolver   = new ControllerResolver();
                $kernel     = new HttpKernel($dispatch, $resolver);
                $response = $kernel->handle($request);
                $response->send();
                $kernel->terminate($request, $response);
                exit(0);
            }catch(\Exception $e){
//                print_r($e);
//                exit;
                // No event found, punt back up to Concrete5 runtime
            }
        }

    }

}