<?php
/**
 * @package    PHP Advanced API Guide
 * @author     Davison Pro <davisonpro.coder@gmail.com>
 * @copyright  2019 DavisonPro
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

namespace BestShop\Slim;

use BestShop\Util\ArrayUtils;
use BestShop\Util\StringUtils;

use Slim\Middleware;

class CorsMiddleware extends Middleware
{
    public function call()
    {
        $corsOptions = $this->getOptions();
        if (ArrayUtils::get($corsOptions, 'enabled', false)) {
            $this->processHeaders();
        }

        if (!$this->app->request()->isOptions()) {
            $this->next->call();
        }
    }

    /**
     * Sets the headers
     */
    protected function processHeaders()
    {
        $response = $this->app->response();
        $corsOptions = $this->getOptions();
        $origin = $this->getOrigin();

        if ($origin) {
            $response->header('Access-Control-Allow-Origin', $origin);

            foreach (ArrayUtils::get($corsOptions, 'headers', []) as $name => $value) {
                // Support two options:
                // 1. [Key, Value]
                // 2. Key => Value
                if (is_array($value)) {
                    // using $value will make name the first value character of $value value
                    $temp = $value;
                    list($name, $value) = $temp;
                }

                $response->header($name, $value);
            }
        }
    }

    /**
     * Gets the header origin
     *
     * This is the origin the header is going to be used
     *
     * There are four different scenario's for possibly returning an
     * Access-Control-Allow-Origin header:
     *
     * 1) null - don't return header
     * 2) '*' - return header '*'
     * 3) {str} - return header {str}
     * 4) [{str}, {str}, {str}] - if origin matches, return header {str}
     *
     * @return string
     */
    protected function getOrigin()
    {
        $corsOptions = $this->getOptions();
        $requestOrigin = $this->app->request()->headers->get('Origin');
        $responseOrigin = null;
        $allowedOrigins = ArrayUtils::get($corsOptions, 'origin', '*');

        if (is_array($requestOrigin)) {
            $requestOrigin = array_shift($requestOrigin);
        }

        if (!is_array($allowedOrigins)) {
            if (is_string($allowedOrigins)) {
                $allowedOrigins = StringUtils::csv($allowedOrigins);
            } else {
                $allowedOrigins = [$allowedOrigins];
            }
        }

        if (in_array($requestOrigin, $allowedOrigins)) {
            $responseOrigin = $requestOrigin;
        } else if (in_array('*', $allowedOrigins)) {
            $responseOrigin = '*';
        }

        return $responseOrigin;
    }

    /**
     * Gets CORS options
     *
     * @return array
     */
    protected function getOptions()
    {
		global $config;
        return ArrayUtils::get($config, 'cors');
    }
}
