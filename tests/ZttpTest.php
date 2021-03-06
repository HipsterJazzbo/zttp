<?php

use Zttp\Zttp;
use PHPUnit\Framework\TestCase;

class ZttpTest extends TestCase
{
    private function url($url)
    {
        return vsprintf('%s/%s', [
            rtrim(getenv('TEST_SERVER_URL'), '/'),
            ltrim($url, '/'),
        ]);
    }

    /** @test */
    public function query_parameters_can_be_passed_as_an_array()
    {
        $response = Zttp::get($this->url('/get'), [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertArraySubset([
            'query' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ]
        ], $response->json());
    }

    /** @test */
    public function query_parameters_in_urls_are_respected()
    {
        $response = Zttp::get($this->url('/get?foo=bar&baz=qux'));

        $this->assertArraySubset([
            'query' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ]
        ], $response->json());
    }

    /** @test */
    public function query_parameters_in_urls_can_be_combined_with_array_parameters()
    {
        $response = Zttp::get($this->url('/get?foo=bar'), [
            'baz' => 'qux'
        ]);

        $this->assertArraySubset([
            'query' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ]
        ], $response->json());
    }

    /** @test */
    public function post_content_is_json_by_default()
    {
        $response = Zttp::post($this->url('/post'), [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertArraySubset([
            'headers' => [
                'content-type' => ['application/json'],
            ],
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ]
        ], $response->json());
    }

    /** @test */
    public function post_content_can_be_sent_as_form_params()
    {
        $response = Zttp::asFormParams()->post($this->url('/post'), [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertArraySubset([
            'headers' => [
                'content-type' => ['application/x-www-form-urlencoded'],
            ],
            'form_params' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ]
        ], $response->json());
    }

    /** @test */
    public function post_content_can_be_sent_as_json_explicitly()
    {
        $response = Zttp::asJson()->post($this->url('/post'), [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertArraySubset([
            'headers' => [
                'content-type' => ['application/json'],
            ],
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ]
        ], $response->json());
    }

    /** @test */
    public function get_with_additional_headers()
    {
        $response = Zttp::withHeaders(['Custom' => 'Header'])->get($this->url('/get'));

        $this->assertArraySubset([
            'headers' => [
                'custom' => ['Header'],
            ],
        ], $response->json());
    }

    /** @test */
    public function post_with_additional_headers()
    {
        $response = Zttp::withHeaders(['Custom' => 'Header'])->post($this->url('/post'));

        $this->assertArraySubset([
            'headers' => [
                'custom' => ['Header'],
            ],
        ], $response->json());
    }

    /** @test */
    public function the_accept_header_can_be_set_via_shortcut()
    {
        $response = Zttp::accept('banana/sandwich')->post($this->url('/post'));

        $this->assertArraySubset([
            'headers' => [
                'accept' => ['banana/sandwich'],
            ],
        ], $response->json());
    }

    /** @test */
    public function exceptions_are_not_thrown_for_40x_responses()
    {
        $response = Zttp::withHeaders(['Z-Status' => 418])->get($this->url('/get'));

        $this->assertEquals(418, $response->status());
    }

    /** @test */
    public function exceptions_are_not_thrown_for_50x_responses()
    {
        $response = Zttp::withHeaders(['Z-Status' => 508])->get($this->url('/get'));

        $this->assertEquals(508, $response->status());
    }

    /** @test */
    public function redirects_are_followed_by_default()
    {
        $response = Zttp::get($this->url('/redirect'));

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Redirected!', $response->body());
    }

    /** @test */
    public function redirects_can_be_disabled()
    {
        $response = Zttp::withoutRedirecting()->get($this->url('/redirect'));

        $this->assertEquals(302, $response->status());
        $this->assertEquals($this->url('/redirected'), $response->header('Location'));
    }

    /** @test */
    public function patch_requests_are_supported()
    {
        $response = Zttp::patch($this->url('/patch'), [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertArraySubset([
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ]
        ], $response->json());
    }

    /** @test */
    public function put_requests_are_supported()
    {
        $response = Zttp::put($this->url('/put'), [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertArraySubset([
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ]
        ], $response->json());
    }

    /** @test */
    public function delete_requests_are_supported()
    {
        $response = Zttp::delete($this->url('/delete'), [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertArraySubset([
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ]
        ], $response->json());
    }

    /** @test */
    public function query_parameters_are_respected_in_post_requests()
    {
        $response = Zttp::post($this->url('/post?banana=sandwich'), [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertArraySubset([
            'query' => [
                'banana' => 'sandwich',
            ],
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ]
        ], $response->json());
    }

    /** @test */
    public function query_parameters_are_respected_in_put_requests()
    {
        $response = Zttp::put($this->url('/put?banana=sandwich'), [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertArraySubset([
            'query' => [
                'banana' => 'sandwich',
            ],
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ]
        ], $response->json());
    }

    /** @test */
    public function query_parameters_are_respected_in_patch_requests()
    {
        $response = Zttp::patch($this->url('/patch?banana=sandwich'), [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertArraySubset([
            'query' => [
                'banana' => 'sandwich',
            ],
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ]
        ], $response->json());
    }

    /** @test */
    public function query_parameters_are_respected_in_delete_requests()
    {
        $response = Zttp::delete($this->url('/delete?banana=sandwich'), [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertArraySubset([
            'query' => [
                'banana' => 'sandwich',
            ],
            'json' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ]
        ], $response->json());
    }

    /** @test */
    public function can_retrieve_the_raw_response_body()
    {
        $response = Zttp::get($this->url('/simple-response'));

        $this->assertEquals("A simple string response", $response->body());
    }

    /** @test */
    public function can_retrieve_response_header_values()
    {
        $response = Zttp::get($this->url('/get'));

        $this->assertEquals('application/json', $response->header('Content-Type'));
    }

    /** @test */
    public function can_check_if_a_response_is_success()
    {
        $response = Zttp::withHeaders(['Z-Status' => 200])->get($this->url('/get'));

        $this->assertTrue($response->isSuccess());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isClientError());
        $this->assertFalse($response->isServerError());
    }

    /** @test */
    public function can_check_if_a_response_is_redirect()
    {
        $response = Zttp::withHeaders(['Z-Status' => 302])->get($this->url('/get'));

        $this->assertTrue($response->isRedirect());
        $this->assertFalse($response->isSuccess());
        $this->assertFalse($response->isClientError());
        $this->assertFalse($response->isServerError());
    }

    /** @test */
    public function can_check_if_a_response_is_client_error()
    {
        $response = Zttp::withHeaders(['Z-Status' => 404])->get($this->url('/get'));

        $this->assertTrue($response->isClientError());
        $this->assertFalse($response->isSuccess());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isServerError());
    }

    /** @test */
    public function can_check_if_a_response_is_server_error()
    {
        $response = Zttp::withHeaders(['Z-Status' => 508])->get($this->url('/get'));

        $this->assertTrue($response->isServerError());
        $this->assertFalse($response->isSuccess());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isClientError());
    }
}
