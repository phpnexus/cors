<?php
/**
 * CORS service
 *
 * @link        https://github.com/phpnexus/cors
 * @copyright   Copyright (c) 2016 Mark Prosser
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 */

namespace PhpNexus\Cors;

use InvalidArgumentException;

class CorsService
{
    /**
     * @var array Config
     */
    protected $config = [
        'allowMethods'     => [],
        'allowHeaders'     => [],
        'allowOrigins'     => [],
        'allowCredentials' => false,
        'exposeHeaders'    => [],
        'maxAge'           => 0,
    ];

    /**
     * @var array Simple methods
     * @see https://www.w3.org/TR/cors/#simple-method
     */
    protected $simpleMethods = [
        'GET',
        'HEAD',
        'POST',
    ];

    /**
     * @var array Simple headers
     * @see https://www.w3.org/TR/cors/#simple-header
     */
    protected $simpleHeaders = [
        'Accept',
        'Accept-Language',
        'Content-Language',
    ];

    /**
     * @param array $config Config
     */
    public function __construct(array $config)
    {
        $this->setConfig($config);
    }

    /**
     * Process request
     *
     * @param CorsRequest $request
     * @return array Response parameters
     */
    public function process(CorsRequest $request)
    {
        $response = [];

        /**
         * Section 6.2 - Preflight Request
         * @see https://www.w3.org/TR/cors/#resource-preflight-requests
         */
        if ($request->isPreflight()) {
            // Section 6.2 #1 - If no origin, stop processing
            if (!$request->hasOrigin()) {
               return $response;
            }

            // Section 6.2 #2 - If origin not allowed, stop processing
            if (!$this->isOriginAllowed($request->getOrigin())) {
                return $response;
            }

            // Section 6.2 #3 - Check access-control-request-method
            if (!$this->checkAccessControlRequestMethod($request->getAccessControlRequestMethod())) {
                return $response;
            }

            // Section 6.2 #4 - Check access-control-request-headers
            if ($request->hasAccessControlRequestHeaders()
            && !$this->checkAccessControlRequestHeaders($request->getAccessControlRequestHeaders())
            ) {
                return $response;
            }

            // Section 6.2 #5 - Check if requested method allowed
            if (!$this->isMethodAllowed($request->getAccessControlRequestMethod())) {
                return $response;
            }

            // Section 6.2 #6 - Check if ALL requested headers are allowed
            if ($request->hasAccessControlRequestHeaders()
            && !$this->isHeadersAllowed($request->getAccessControlRequestHeaders())
            ) {
                return $response;
            }

            // Section 6.2 #7 - Add allowed origin to response parameters
            $response['access-control-allow-origin'] = $request->getOrigin();

            // Section 6.2 #7 - Add allow credentials to response parameters
            if ($this->canAllowCredentials()) {
                // Add credentials flag to response parameters
                $response['access-control-allow-credentials'] = 'true';
            }

            // Section 6.2 #8 - Optionally add max-age
            if ($this->canCache()) {
                // Add max age to response parameters
                $response['max-age'] = (string)$this->config['maxAge'];
            }

            // Section 6.2 #9 - If request method is NOT simple method
            if (!$this->isSimpleMethod($request->getAccessControlRequestMethod())) {
                // Add allowed methods to response parameters
                $response['access-control-allow-methods'] = $this->config['allowMethods'];
            }

            // Section 6.2 #10 - If request headers is NOT simple header, or request headers contains Content-Type
            if (!$this->isSimpleHeaders($request->getAccessControlRequestHeaders())) {
                // Add allowed headers to response parameters
                $response['access-control-allow-headers'] = $this->config['allowHeaders'];
            }
        }
        /**
         * Section 6.1 - Simple Cross Origin Request, Actual Request, and Redirects
         * @see https://www.w3.org/TR/cors/#resource-requests
         */
        else {
            // Section 6.1 #1 - If no origin, stop processing
            if (!$request->hasOrigin()) {
               return $response;
            }

            // Section 6.1 #2 - If origin not allowed, stop processing
            if (!$this->isOriginAllowed($request->getOrigin())) {
                return $response;
            }

            // Section 6.1 #3 - Add allowed origin to response parameters
            $response['access-control-allow-origin'] = $request->getOrigin();

            // Section 6.1 #3 - Add allow credentials to response parameters
            if ($this->canAllowCredentials()) {
                // Add credentials flag to response parameters
                $response['access-control-allow-credentials'] = 'true';
            }

            // Section 6.1 #4 - Add exposed headers to response parameters
            if ($this->canExposeHeaders()) {
                // Add exposable headers to response parameters
                $response['access-control-expose-headers'] = $this->config['exposeHeaders'];
            }
        }

        return $response;
    }

    /**
     * Set config
     *
     * @param array $config Config
     * @return self
     */
    public function setConfig(array $config)
    {
        // Filter out unknown config keys, and use to override defaults
        $config = array_merge(
            $this->config,
            array_intersect_key($config, $this->config)
        );

        // Normalize allowMethods
        if (is_string($config['allowMethods'])) {
            $config['allowMethods'] = [$config['allowMethods']];
        }

        // Normalize allowHeaders
        if (is_string($config['allowHeaders'])) {
            $config['allowHeaders'] = [$config['allowHeaders']];
        }

        // Normalize allowOrigins
        if (is_string($config['allowOrigins'])) {
            $config['allowOrigins'] = [$config['allowOrigins']];
        }

        // Normalize allowCredentials
        $config['allowCredentials'] = (bool)$config['allowCredentials'];

        // Normalize maxAge
        $config['maxAge'] = (int)$config['maxAge'];

        $this->config = $config;

        return $this;
    }

    /**
     * Get config
     *
     * @param string $key Config key (optional)
     * @return mixed
     */
    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->config;
        }
        elseif (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }
        else {
            return; // Or throw exception?
        }
    }

    /**
     * Check if Access-Control-Request-Method is valid
     * Section 5.8
     *
     * @param string $accessControlRequestMethod
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function checkAccessControlRequestMethod($accessControlRequestMethod)
    {
        // Make sure $accessControlRequestMethod is string
        if (!is_string($accessControlRequestMethod)) {
            throw new InvalidArgumentException(
                '$accessControlRequestMethod must be string'
            );
        }

        return $this->isValidMethod($accessControlRequestMethod);
    }

    /**
     * Is method valid?
     * @see https://tools.ietf.org/html/rfc2616#section-5.1.1
     *
     * @param string $method
     * @return bool
     */
    protected function isValidMethod($method)
    {
        return is_string($method) && $this->isValidToken($method);
    }

    /**
     * Check if text is valid "token"
     *
     * @see https://tools.ietf.org/html/rfc2616#section-2.2
     *
     * @param string $text
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function isValidToken($text)
    {
        // Make sure $text is string
        if (!is_string($text)) {
            throw new InvalidArgumentException(
                '$text must be string'
            );
        }

        $separators = [
            '(', ')', '<', '>', '@',
            ',', ';', ':', '\\', '"',
            '/', '[', ']', '?', '=',
            '{', '}', 32, 9
        ];

        // If contains separator, return false
        foreach ($separators as $s) {
            if (strpos($text, $s) !== false) {
                return false;
            }
        }

        // If contains control character, return false
        if (ctype_cntrl($text)) {
            return false;
        }

        return true;
    }

    /**
     * Check if Access-Control-Request-Headers is valid
     * Section 5.9
     *
     * @param array $accessControlRequestHeaders
     * @return bool
     */
    protected function checkAccessControlRequestHeaders(array $accessControlRequestHeaders)
    {
        // Make sure all array elements are strings and not blank
        foreach ($accessControlRequestHeaders as $h) {
            if (!is_string($h) || $h === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if request method is allowed
     *
     * @param string $method
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function isMethodAllowed($method)
    {
        // Make sure $method is string
        if (!is_string($method)) {
            throw new InvalidArgumentException(
                '$method must be string'
            );
        }

        // If method is simple method, always allow
        if ($this->isSimpleMethod($method)) {
            return true;
        }

        return in_array(
            $method,
            $this->config['allowMethods'],
            true
        );
    }

    /**
     * Check if ALL request headers are allowed
     *
     * @param array $headers
     * @return bool
     */
    protected function isHeadersAllowed(array $headers)
    {
        /**
         * Webkit browsers are known to include simple headers in access-control-request-headers.
         * We need to remove these so they aren't considered in the check for allowed headers.
         * @see http://stackoverflow.com/questions/17330302/why-non-custom-headers-in-access-control-request-headers
         */
        // Strip out any requests for simple headers
        $headers = array_udiff(
            $headers,
            $this->simpleHeaders,
            'strcasecmp'
        );

        // Check for any access-control-request-headers not in allowed headers, using case-insensitive comparison
        return array_udiff(
            $headers,
            $this->config['allowHeaders'],
            'strcasecmp'
        ) === [];
    }

    /**
     * Check if request origin is allowed
     * Section 6.1 #2, 6.2 #2
     *
     * @param string $origin
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function isOriginAllowed($origin)
    {
        // Make sure $origin is string
        if (!is_string($origin)) {
            throw new InvalidArgumentException(
                '$origin must be string'
            );
        }

        // If asterisk is used for allowed origins, always return true
        if (in_array('*', $this->config['allowOrigins'])) {
            return true;
        }

        // Check if origin is in list of allowed origins
        return in_array($origin, $this->config['allowOrigins'], true);
    }

    /**
     * Is method a simple method?
     *
     * @param string $method
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function isSimpleMethod($method)
    {
        // Make sure $method is string
        if (!is_string($method)) {
            throw new InvalidArgumentException(
                '$method must be string'
            );
        }

        return in_array($method, $this->simpleMethods);
    }

    /**
     * Are all headers simple headers?
     *
     * @param array $headers
     * @return bool
     */
    protected function isSimpleHeaders(array $headers)
    {
        return array_udiff(
            $headers,
            $this->simpleHeaders,
            'strcasecmp'
        ) === [];
    }

    /**
     * Can allow credentials?
     * Section 6.1 #3, 6.2 #7 - Credentials are not allowed if an asterisk is used for allowed origins
     *
     * @return bool
     */
    protected function canAllowCredentials()
    {
        return !in_array('*', $this->config['allowOrigins'], true)
        && (bool)$this->config['allowCredentials'];
    }

    /**
     * Can expose headers?
     *
     * @return bool
     */
    protected function canExposeHeaders()
    {
        return !empty($this->config['exposeHeaders']);
    }

    /**
     * Can cache?
     *
     * @return bool
     */
    protected function canCache()
    {
        return $this->config['maxAge'] > 0;
    }
}
