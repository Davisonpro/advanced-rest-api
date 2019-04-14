<?php
/**
 * @package    PHP Advanced API Guide
 * @author     Davison Pro <davisonpro.coder@gmail.com>
 * @copyright  2019 DavisonPro
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

namespace BestShop;

use Slim\Http\Util;
use Slim\Slim;
use BestShop\Util\ArrayUtils;
use BestShop\Slim\BaseResponse;
use BestShop\Slim\BaseRequest;
use BestShop\Slim\Environment;

/**
 * Api
 */
class Api extends Slim {
	/**
     * @var Api
     */
	public static $instance = null;

	/**
     * @var bool
     */
    protected $booted = false;
	
	/**
     * Constructor
     */
    public function __construct( array $appSettings  ) {
		parent::__construct($appSettings);

        $this->container->singleton('environment', function () {
            return Environment::getInstance();
        });

        $this->container->singleton('response', function () {
            return new BaseResponse();
        });

        // Default request
        $this->container->singleton('request', function ($c) {
            return new BaseRequest($c['environment']);
		});
		
        $this->hook('slim.before.router', [$this, 'guessOutputFormat']);
        
		self::$instance = $this;
        $this->booted = true;
	}

	public function response()
    {
        $response = parent::response();
        if (func_num_args() > 0) {
			$data = ArrayUtils::get(func_get_args(), 0);
            $response->header('Content-Type', 'application/json; charset=utf-8');
            $response->setBody(json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        return $response;
    }

    /**
     * @inheritdoc
     */
    protected function mapRoute($args)
    {
        $pattern = array_shift($args);
        $callable = $this->resolveCallable(array_pop($args));
        $route = new \Slim\Route($pattern, $callable);
        $this->router->map($route);
        if (count($args) > 0) {
            $route->setMiddleware($args);
        }

        return $route;
	}
	
	/**
     * Resolve toResolve into a closure that that the router can dispatch.
     *
     * If toResolve is of the format 'class:method', then try to extract 'class'
     * from the container otherwise instantiate it and then dispatch 'method'.
     *
     * @param mixed $toResolve
     *
     * @return callable
     *
     * @throws \RuntimeException if the callable does not exist
     * @throws \RuntimeException if the callable is not resolvable
     */
    public function resolveCallable($toResolve)
    {
        $resolved = $toResolve;

        if (!is_callable($toResolve) && is_string($toResolve)) {
            // check for slim callable as "class:method"
            $callablePattern = '!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';
            if (preg_match($callablePattern, $toResolve, $matches)) {
                $class = $matches[1];
                $method = $matches[2];

                if ($this->container->has($class)) {
                    $resolved = [$this->container->get($class), $method];
                } else {
                    if (!class_exists($class)) {
                        throw new \RuntimeException(sprintf('Callable %s does not exist', $class));
                    }
                    $resolved = [new $class($this), $method];
                }
            } else {
                // check if string is something in the DIC that's callable or is a class name which
                // has an __invoke() method
                $class = $toResolve;
                if ($this->container->has($class)) {
                    $resolved = $this->container->get($class);
                } else {
                    if (!class_exists($class)) {
                        throw new \RuntimeException(sprintf('Callable %s does not exist', $class));
                    }
                    $resolved = new $class($this);
                }
            }
        }

        if (!is_callable($resolved)) {
            throw new \RuntimeException(sprintf(
                '%s is not resolvable',
                is_array($toResolve) || is_object($toResolve) ? json_encode($toResolve) : $toResolve
            ));
        }

        return $resolved;
    }

    protected function guessOutputFormat()
    {
        $api = $this;
        $outputFormat = 'json';
        $requestUri = $api->request->getResourceUri();

        if ($this->requestHasOutputFormat()) {
            $outputFormat = $this->getOutputFormat();
            // @TODO: create a replace last/first ocurrence
            $pos = strrpos($requestUri, '.' . $outputFormat);
            $newRequestUri = substr_replace($requestUri, '', $pos, strlen('.' . $outputFormat));
            $env = $api->environment();
            $env['PATH_INFO'] = $newRequestUri;
        }

        return $outputFormat;
    }

    protected function requestHasOutputFormat()
    {
        $matches = $this->getOutputFormat();

        return $matches ? true : false;
    }

    protected function getOutputFormat()
    {
        $requestUri = trim($this->request->getResourceUri(), '/');

        // @TODO: create a startsWith and endsWith using regex
        $matches = [];
        preg_match('#\.[\w]+$#', $requestUri, $matches);

        return isset($matches[0]) ? substr($matches[0], 1) : null;
	}
}