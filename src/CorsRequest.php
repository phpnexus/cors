<?php
/**
 * CORS request
 *
 * @link        https://github.com/phpnexus/cors
 * @copyright   Copyright (c) 2016 Mark Prosser
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 */

namespace PhpNexus\Cors;

use InvalidArgumentException;

class CorsRequest
{
    /** @var string Request method */
    protected $method = '';

    /** @var string Origin */
    protected $origin = '';

    /** @var string Access-control-request-method */
    protected $accessControlRequestMethod = '';

    /** @var array Access-control-request-headers */
    protected $accessControlRequestHeaders = [];

    /**
     * @param string $method (optional)
     * @param string $origin (optional)
     * @param string $accessControlRequestMethod (optional)
     * @param array $accessControlRequestHeaders (optional)
     */
    public function __construct($method = null, $origin = null, $accessControlRequestMethod = null, array $accessControlRequestHeaders = null)
    {
        // Set method
        if ($method !== null) {
            $this->setMethod($method);
        }

        // Set origin (optional)
        if ($origin !== null) {
            $this->setOrigin($origin);
        }

        // Set access-control-request-method (optional)
        if ($accessControlRequestMethod !== null) {
            $this->setAccessControlRequestMethod($accessControlRequestMethod);
        }

        // Set access-control-request-headers (optional)
        if ($accessControlRequestHeaders !== null) {
            $this->setAccessControlRequestHeaders($accessControlRequestHeaders);
        }
    }

    /**
     * Is this a preflight request?
     *
     * @return bool
     */
    public function isPreflight()
    {
        return $this->getMethod() === 'OPTIONS'
        && $this->hasAccessControlRequestMethod();
    }

    /**
     * Set method
     *
     * @param string $method
     * @return self
     * @throws InvalidArgumentException
     */
    public function setMethod($method)
    {
        // Validate method is string
        if (is_string($method) === false) {
            throw new InvalidArgumentException(
                'Parameter "method" is not a string'
            );
        }

        // Valid method is not blank
        if ($method === '') {
            throw new InvalidArgumentException(
                'Parameter "method" cannot be blank'
            );
        }

        $this->method = $method;

        return $this;
    }

    /**
     * Get method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set origin
     *
     * @param string $origin
     * @return self
     * @throws InvalidArgumentException
     */
    public function setOrigin($origin)
    {
        // Validate origin is only a scheme and hostname
        $parsed_origin = parse_url($origin);
        if (array_diff_key(['scheme' => true, 'host' => true], $parsed_origin) !== []) {
            throw new InvalidArgumentException(
                'Parameter "origin" is not in the format {scheme}://{host}'
            );
        }

        $this->origin = $origin;

        return $this;
    }

    /**
     * Get origin
     *
     * @return string|null
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Has origin
     *
     * @return bool
     */
    public function hasOrigin()
    {
        return $this->origin !== '';
    }

    /**
     * Set access-control-request-method
     *
     * @param string $requestMethod
     * @return self
     */
    public function setAccessControlRequestMethod($accessControlRequestMethod)
    {
        // Validate access-control-request-method is string
        if (is_string($accessControlRequestMethod) === false) {
            throw new InvalidArgumentException(
                'Parameter "method" is not a string'
            );
        }

        // Valid access-control-request-method is not blank
        if ($accessControlRequestMethod === '') {
            throw new InvalidArgumentException(
                'Parameter "method" cannot be blank'
            );
        }

        $this->accessControlRequestMethod = $accessControlRequestMethod;

        return $this;
    }

    /**
     * Get access-control-request-method
     *
     * @return string|null
     */
    public function getAccessControlRequestMethod()
    {
        return $this->accessControlRequestMethod;
    }

    /**
     * Has access-control-request-method
     *
     * @return bool
     */
    public function hasAccessControlRequestMethod()
    {
        return $this->accessControlRequestMethod !== '';
    }

    /**
     * Set access-control-request-headers
     *
     * @param array $requestHeaders
     * @return self
     */
    public function setAccessControlRequestHeaders(array $accessControlRequestHeaders)
    {
        // Validate all array elements are strings
        foreach ($accessControlRequestHeaders as $h) {
            if (is_string($h) === false) {
                throw new InvalidArgumentException(
                    'An element in array "accessControlRequestHeaders" is not a string'
                );
            }
        }

        $this->accessControlRequestHeaders = $accessControlRequestHeaders;

        return $this;
    }

    /**
     * Get access-control-request-headers
     *
     * @return string[]|null
     */
    public function getAccessControlRequestHeaders()
    {
        return $this->accessControlRequestHeaders;
    }

    /**
     * Has access-control-request-headers
     *
     * @return bool
     */
    public function hasAccessControlRequestHeaders()
    {
        return $this->accessControlRequestHeaders !== [];
    }
}
