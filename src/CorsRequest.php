<?php
/**
 * CORS request
 *
 * @link        https://github.com/phpnexus/cors
 * @copyright   Copyright (c) 2020 Mark Prosser
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 */

namespace PhpNexus\Cors;

use InvalidArgumentException;

class CorsRequest
{
    /** @var string Request method */
    private $method = '';

    /** @var string Origin */
    private $origin = '';

    /** @var string Access-control-request-method */
    private $accessControlRequestMethod = '';

    /** @var array Access-control-request-headers */
    private $accessControlRequestHeaders = [];

    /**
     * @param string $method (optional)
     * @param string $origin (optional)
     * @param string $accessControlRequestMethod (optional)
     * @param array $accessControlRequestHeaders (optional)
     */
    public function __construct(string $method = null, string $origin = null, string $accessControlRequestMethod = null, array $accessControlRequestHeaders = null)
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
    public function isPreflight(): bool
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
    public function setMethod(string $method): CorsRequest
    {
        // Validate method is not blank
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
    public function getMethod(): string
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
    public function setOrigin(string $origin): CorsRequest
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
    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    /**
     * Has origin
     *
     * @return bool
     */
    public function hasOrigin(): bool
    {
        return $this->origin !== '';
    }

    /**
     * Set access-control-request-method
     *
     * @param string $requestMethod
     * @return self
     */
    public function setAccessControlRequestMethod(string $accessControlRequestMethod): CorsRequest
    {
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
    public function getAccessControlRequestMethod(): ?string
    {
        return $this->accessControlRequestMethod;
    }

    /**
     * Has access-control-request-method
     *
     * @return bool
     */
    public function hasAccessControlRequestMethod(): bool
    {
        return $this->accessControlRequestMethod !== '';
    }

    /**
     * Set access-control-request-headers
     *
     * @param array $requestHeaders
     * @return self
     */
    public function setAccessControlRequestHeaders(array $accessControlRequestHeaders): CorsRequest
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
     * @return array
     */
    public function getAccessControlRequestHeaders(): array
    {
        return $this->accessControlRequestHeaders;
    }

    /**
     * Has access-control-request-headers
     *
     * @return bool
     */
    public function hasAccessControlRequestHeaders(): bool
    {
        return $this->accessControlRequestHeaders !== [];
    }
}
