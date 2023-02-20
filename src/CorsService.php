<?php
/**
 * CORS service
 *
 * @link        https://github.com/phpnexus/cors
 * @copyright   Copyright (c) 2020 Mark Prosser
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 */

namespace PhpNexus\Cors;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;

class CorsService implements LoggerAwareInterface
{
    /**
     * @var array Config
     */
    private $config = [
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
    private $simpleMethods = [
        'GET',
        'HEAD',
        'POST',
    ];

    /**
     * @var array Simple headers
     * @see https://www.w3.org/TR/cors/#simple-header
     *
     * "Origin" is not officially a simple header, but Safari always includes
     * it with non-simple requests, and it is a critical part of CORS.
     */
    private $simpleHeaders = [
        'Accept',
        'Accept-Language',
        'Content-Language',
        'Origin',
    ];

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @param array $config Config
     * @param \Psr\Log\LoggerInterface $logger Logger instance
     */
    public function __construct(array $config, LoggerInterface $logger = null)
    {
        $this->setConfig($config);

        if ($logger !== null) {
            $this->setLogger($logger);
        }
    }

    /**
     * Process request
     *
     * @param CorsRequest $request
     * @return array Response parameters
     */
    public function process(CorsRequest $request): array
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
                if ($this->logger) {
                    $this->logger->info('Origin now allowed');
                }
                return $response;
            }

            // Section 6.2 #3 - Check access-control-request-method
            if (!$this->checkAccessControlRequestMethod($request->getAccessControlRequestMethod())) {
                if ($this->logger) {
                    $this->logger->info('Header "access-control-request-method" is not valid');
                }
                return $response;
            }

            // Section 6.2 #4 - Check access-control-request-headers
            if ($request->hasAccessControlRequestHeaders()
            && !$this->checkAccessControlRequestHeaders($request->getAccessControlRequestHeaders())
            ) {
                if ($this->logger) {
                    $this->logger->info('Header "access-control-request-headers" is not valid');
                }
                return $response;
            }

            // Section 6.2 #5 - Check if requested method allowed
            if (!$this->isMethodAllowed($request->getAccessControlRequestMethod())) {
                if ($this->logger) {
                    $this->logger->info('Method is not allowed');
                }
                return $response;
            }

            // Section 6.2 #6 - Check if ALL requested headers are allowed
            if ($request->hasAccessControlRequestHeaders()
            && !$this->isHeadersAllowed($request->getAccessControlRequestHeaders())
            ) {
                if ($this->logger) {
                    $this->logger->info('Headers not in "allow-control-request-headers" list');
                }
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
                if ($this->logger) {
                    $this->logger->info('Origin now allowed');
                }
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
    public function setConfig(array $config): CorsService
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

        /** @todo Check for use of wildcard in allowOrigins when allowCredentials equals true */

        // Set config as object attribute
        $this->config = $config;

        return $this;
    }

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Check if Access-Control-Request-Method is valid
     * Section 5.8
     *
     * @param string $accessControlRequestMethod
     * @return bool
     */
    protected function checkAccessControlRequestMethod(string $accessControlRequestMethod): bool
    {
        return $this->isValidMethod($accessControlRequestMethod);
    }

    /**
     * Is method valid?
     * @see https://tools.ietf.org/html/rfc2616#section-5.1.1
     *
     * @param string $method
     * @return bool
     */
    protected function isValidMethod(string $method): bool
    {
        return $this->isValidToken($method);
    }

    /**
     * Check if text is valid "token"
     *
     * @see https://tools.ietf.org/html/rfc2616#section-2.2
     *
     * @param string $text
     * @return bool
     */
    protected function isValidToken(string $text): bool
    {
        // List of separators
        $separators = [
            '(', ')', '<', '>', '@',
            ',', ';', ':', '\\', '"',
            '/', '[', ']', '?', '=',
            '{', '}', chr(32), chr(9)
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
    protected function checkAccessControlRequestHeaders(array $accessControlRequestHeaders): bool
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
     */
    protected function isMethodAllowed(string $method): bool
    {
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
    protected function isHeadersAllowed(array $headers): bool
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
     */
    protected function isOriginAllowed(string $origin): bool
    {
        // If asterisk is used for allowed origins
        if ($this->config['allowOrigins'] === ['*']) {
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
     */
    protected function isSimpleMethod(string $method): bool
    {
        return in_array($method, $this->simpleMethods);
    }

    /**
     * Are all headers simple headers?
     *
     * @param array $headers
     * @return bool
     */
    protected function isSimpleHeaders(array $headers): bool
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
    protected function canAllowCredentials(): bool
    {
        return $this->config['allowOrigins'] !== ['*']
        && (bool)$this->config['allowCredentials'];
    }

    /**
     * Can expose headers?
     *
     * @return bool
     */
    protected function canExposeHeaders(): bool
    {
        return !empty($this->config['exposeHeaders']);
    }

    /**
     * Can cache?
     *
     * @return bool
     */
    protected function canCache(): bool
    {
        return $this->config['maxAge'] > 0;
    }
}
