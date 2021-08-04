<?php
/**
 * CORS service test
 *
 * @link        https://github.com/phpnexus/cors
 * @copyright   Copyright (c) 2016 Mark Prosser
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 */

namespace PhpNexus\Cors\Tests;

use PhpNexus\Cors\CorsService;
use PhpNexus\Cors\CorsRequest;
use PHPUnit\Framework\TestCase;

class CorsServiceTest extends TestCase
{
    /**
     * CORS:
     * - allowOrigins: http://example.com
     * Actual request:
     * - Without origin
     * Result: empty
     */
    public function test_section_6_1_1()
    {
        $cors = new CorsService([
            'allowOrigins' => ['http://example.com'],
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('GET')
        );

        $this->assertEquals([], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * Actual request
     * - Origin: http://bad.example.com
     * Result: empty
     */
    public function test_section_6_1_2()
    {
        $cors = new CorsService([
            'allowOrigins' => ['http://example.com'],
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('GET')
            ->setOrigin('http://bad.example.com')
        );

        $this->assertEquals([], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * - allowCredentials: false
     * Actual request
     * - Origin: http://example.com
     * Result:
     * - Contains access-control-allow-origin key, with allowed origin as value
     */
    public function test_section_6_1_3()
    {
        $cors = new CorsService([
            'allowOrigins'     => ['http://example.com'],
            'allowCredentials' => false,
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('GET')
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([
            'access-control-allow-origin' => 'http://example.com',
        ], $result);
    }

    /**
     * CORS:
     * - allowOrigins: *
     * - allowCredentials: false
     * Actual request:
     * - Origin: http://example.com
     * Result:
     * - Contains access-control-allow-origin key, with allowed origin as value
     */
    public function test_section_6_1_3_wildcard()
    {
        $cors = new CorsService([
            'allowOrigins'     => ['*'],
            'allowCredentials' => false,
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('GET')
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([
            'access-control-allow-origin' => 'http://example.com',
        ], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * - allowCredentials: true
     * Actual request:
     * - Origin: http://example.com
     * Result:
     * - Contains access-control-allow-origin key, with allowed origin as value
     * - Contains access-control-allow-credentials key, with "true" as value
     */
    public function test_section_6_1_3_credentials()
    {
        $cors = new CorsService([
            'allowOrigins'     => ['http://example.com'],
            'allowCredentials' => true,
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('GET')
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([
            'access-control-allow-origin'      => 'http://example.com',
            'access-control-allow-credentials' => 'true',
        ], $result);
    }

    /**
     * CORS:
     * - allowOrigins: *
     * - allowCredentials: true
     * Actual request:
     * - Origin: http://example.com
     * Result:
     * - Contains access-control-allow-origin key, with allowed origin as value
     * - Does NOT contain access-control-allow-credentials key (cannot be used with "allowOrigins: *")
     */
    public function test_section_6_1_3_wildcard_credentials()
    {
        $cors = new CorsService([
            'allowOrigins'     => ['*'],
            'allowCredentials' => true,
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('GET')
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([
            'access-control-allow-origin' => 'http://example.com',
        ], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * - exposeHeaders: X-My-Custom-Header
     * Actual request:
     * - Origin: http://example.com
     * Result:
     * - Contains access-control-allow-origin key, with allowed origin as value
     * - Contains access-control-expose-headers key, with exposed headers as value
     */
    public function test_section_6_1_4()
    {
        $cors = new CorsService([
            'allowOrigins'  => ['http://example.com'],
            'exposeHeaders' => ['X-My-Custom-Header'],
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('GET')
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([
            'access-control-allow-origin'   => 'http://example.com',
            'access-control-expose-headers' => ['X-My-Custom-Header'],
        ], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * Preflight request:
     * - Access-Control-Request-Method: PUT
     * - Without origin
     * Result: empty
     */
    public function test_section_6_2_1()
    {
        $cors = new CorsService([
            'allowOrigins'  => ['http://example.com'],
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('PUT')
        );

        $this->assertEquals([], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * Preflight request:
     * - Access-Control-Request-Method: PUT
     * - Origin: http://bad.example.com
     * Result: empty
     */
    public function test_section_6_2_2()
    {
        $cors = new CorsService([
            'allowOrigins' => ['http://example.com'],
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('PUT')
            ->setOrigin('http://bad.example.com')
        );

        $this->assertEquals([], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * - allowMethods: GET ALL
     * Preflight request:
     * - Access-Control-Request-Method: GET ALL
     * - Origin: http://example.com
     * Result: empty
     */
    public function test_section_6_2_3()
    {
        $cors = new CorsService([
            'allowOrigins' => ['http://example.com'],
            'allowMethods' => ['GET ALL']
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('GET ALL')
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * Preflight request:
     * - Access-Control-Request-Method: GET
     * - Access-Control-Request-Headers:
     * Result: empty
     */
    public function test_section_6_2_4()
    {
        $cors = new CorsService([
            'allowOrigins' => ['http://example.com'],
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('GET')
            ->setAccessControlRequestHeaders([''])
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * - allowMethods: PUT
     * Preflight request:
     * - Access-Control-Request-Method: PATCH
     * - Origin: http://example.com
     * Result: empty
     */
    public function test_section_6_2_5()
    {
        $cors = new CorsService([
            'allowOrigins' => ['http://example.com'],
            'allowMethods' => ['PUT']
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('PATCH')
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * - allowHeaders: Content-Type
     * Preflight request:
     * - Access-Control-Request-Method: GET
     * - Access-Control-Request-Headers: Authorization, Content-Type
     * - Origin: http://example.com
     * Result: empty
     */
    public function test_section_6_2_6()
    {
        $cors = new CorsService([
            'allowOrigins' => ['http://example.com'],
            'allowHeaders' => ['Content-Type']
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('GET')
            ->setAccessControlRequestHeaders(['Authorization', 'Content-Type'])
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * - allowCredentials: false
     * Preflight request:
     * - Access-Control-Request-Method: GET
     * - Origin: http://example.com
     * Result:
     * - Contains access-control-allow-origin key, with allowed origin as value
     */
    public function test_section_6_2_7()
    {
        $cors = new CorsService([
            'allowOrigins'     => ['http://example.com'],
            'allowCredentials' => false,
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('GET')
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([
            'access-control-allow-origin' => 'http://example.com',
        ], $result);
    }

    /**
     * CORS:
     * - allowOrigins: *
     * - allowCredentials: false
     * Preflight request:
     * - Access-Control-Request-Method: GET
     * - Origin: http://example.com
     * Result:
     * - Contains access-control-allow-origin key, with allowed origin as value
     */
    public function test_section_6_2_7_wildcard()
    {
        $cors = new CorsService([
            'allowOrigins' => ['*'],
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('GET')
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([
            'access-control-allow-origin' => 'http://example.com',
        ], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * - allowCredentials: true
     * Preflight request:
     * - Access-Control-Request-Method: GET
     * - Origin: http://example.com
     * Result:
     * - Contains access-control-allow-origin key, with allowed origin as value
     * - Contains access-control-allow-credentials key, with "true" as value
     */
    public function test_section_6_2_7_credentials()
    {
        $cors = new CorsService([
            'allowOrigins'     => ['http://example.com'],
            'allowCredentials' => true,
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('GET')
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([
            'access-control-allow-origin'      => 'http://example.com',
            'access-control-allow-credentials' => 'true',
        ], $result);
    }

    /**
     * CORS:
     * - allowOrigins: *
     * - allowCredentials: true
     * Preflight request:
     * - Access-Control-Request-Method: GET
     * - Origin: http://example.com
     * Result:
     * - Contains access-control-allow-origin key, with allowed origin as value
     * - Does NOT contain access-control-allow-credentials key (cannot be used with "allowOrigins: *")
     */
    public function test_section_6_2_7_wildcard_credentials()
    {
        $cors = new CorsService([
            'allowOrigins'     => ['*'],
            'allowCredentials' => true,
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('GET')
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([
            'access-control-allow-origin' => 'http://example.com',
        ], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * - max-age: 3600
     * Preflight request:
     * - Access-Control-Request-Method: GET
     * - Origin: http://example.com
     * Result:
     * - Contains access-control-allow-origin key, with allowed origin as value
     * - Contains max-age key, with max age as value
     */
    public function test_section_6_2_8()
    {
        $cors = new CorsService([
            'allowOrigins' => ['http://example.com'],
            'maxAge'       => 3600,
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('GET')
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([
            'access-control-allow-origin' => 'http://example.com',
            'max-age'                     => 3600,
        ], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * Preflight request:
     * - Access-Control-Request-Method: GET
     * - Origin: http://example.com
     * Result:
     * - Contains access-control-allow-origin key, with allowed origin as value
     * - Does NOT contain access-control-allow-methods key (as only simple method was listed in "Access-Control-Request-Method")
     */
    public function test_section_6_2_9_simple()
    {
        $cors = new CorsService([
            'allowOrigins' => ['http://example.com'],
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('GET')
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([
            'access-control-allow-origin' => 'http://example.com',
        ], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * - allowMethods: PATCH
     * Preflight request:
     * - Access-Control-Request-Method: PATCH
     * - Origin: http://example.com
     * Result:
     * - Contains access-control-allow-origin key, with allowed origin as value
     * - Contains access-control-allow-methods key, with allowed methods as value
     */
    public function test_section_6_2_9_not_simple()
    {
        $cors = new CorsService([
            'allowOrigins' => ['http://example.com'],
            'allowMethods' => ['PATCH'],
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('PATCH')
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([
            'access-control-allow-origin'  => 'http://example.com',
            'access-control-allow-methods' => ['PATCH'],
        ], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * - allowHeaders: Authorization, Content-Type
     * Preflight request:
     * - Access-Control-Request-Method: GET
     * - Access-Control-Request-Headers: Accept, Accept-Language, Content-Language
     * - Origin: http://example.com
     * Result:
     * - Contains access-control-allow-origin key, with allowed origin as value
     * - Does NOT contain access-control-allow-headers key (as only simple headers were listed in "Access-Control-Request-Headers")
     */
    public function test_section_6_2_10_simple()
    {
        $cors = new CorsService([
            'allowOrigins' => ['http://example.com'],
            'allowHeaders' => ['Authorization', 'Content-Type'],
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('GET')
            ->setAccessControlRequestHeaders(['Accept', 'Accept-Language', 'Content-Language', 'Origin'])
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([
            'access-control-allow-origin' => 'http://example.com',
        ], $result);
    }

    /**
     * CORS:
     * - allowOrigins: http://example.com
     * - allowHeaders: Authorization, Content-Type
     * Preflight request:
     * - Access-Control-Request-Method: GET
     * - Access-Control-Request-Headers: Accept, Accept-Language, Authorization, Content-Language, Content-Type
     * - Origin: http://example.com
     * Result:
     * - Contains access-control-allow-origin key, with allowed origin as value
     * - Contains access-control-allow-headers key, with allowed headers as value
     */
    public function test_section_6_2_10_not_simple()
    {
        $cors = new CorsService([
            'allowOrigins' => ['http://example.com'],
            'allowHeaders' => ['Authorization', 'Content-Type'],
        ]);

        $result = $cors->process((new CorsRequest)
            ->setMethod('OPTIONS')
            ->setAccessControlRequestMethod('GET')
            ->setAccessControlRequestHeaders(['Accept', 'Authorization', 'Content-Type'])
            ->setOrigin('http://example.com')
        );

        $this->assertEquals([
            'access-control-allow-origin'  => 'http://example.com',
            'access-control-allow-headers' => ['Authorization', 'Content-Type'],
        ], $result);
    }
}
