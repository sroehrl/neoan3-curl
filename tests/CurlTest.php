<?php

namespace Neoan3\Apps;

use PHPUnit\Framework\TestCase;

/*
 * USED test api is https://jsonplaceholder.typicode.com/guide.html
 * */

class CurlTest extends TestCase
{
    public $baseUrl = 'https://jsonplaceholder.typicode.com/';

    public function testPost()
    {
        $should = ['title' => 'neoan3', 'body' => 'test', 'userId' => 1];
        $response = Curl::post($this->baseUrl . 'posts', $should);
        // expect to add id 101
        $should['id'] = 101;
        $this->assertSame($should, $response);
    }

    public function testGet()
    {
        $response = Curl::get($this->baseUrl . 'posts', ['userId' => 1]);
        $this->assertSame(1, $response[0]['userId']);
    }

    public function testPut()
    {
        $update = ['title' => 'neoan3', 'body' => 'test', 'userId' => 1, 'id' => 1];
        $response = Curl::put($this->baseUrl . 'posts/1', $update);
        $this->assertSame($update, $response);
    }

    public function testCurling()
    {
        $should = ['title' => 'neoan3', 'body' => 'test', 'userId' => 1];
        $response = Curl::curling($this->baseUrl . 'posts', json_encode($should), ['Content-Type: application/json']);
        // expect to add id 101
        $should['id'] = 101;
        $this->assertSame($should, $response);
    }

    public function testCall()
    {
        $should = ['title' => 'neoan3', 'body' => 'test', 'userId' => 1];
        $response = Curl::call($this->baseUrl . 'posts', $should, 'fake-token');
        // expect to add id 101
        $should['id'] = 101;
        $this->assertSame($should, $response);
    }

    public function testExpection()
    {
        $this->expectException(CurlException::class);
        Curl::get('thisCantexist');
    }

    public function testSetResponseFormatVerbose()
    {
        Curl::setResponseFormatVerbose();
        $response = Curl::get($this->baseUrl .'/posts');
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('body', $response);
        $this->assertArrayHasKey('headers', $response);
    }
    public function testSetResponseFormatPlain()
    {
        Curl::setResponseFormatPlain();
        $response = Curl::get($this->baseUrl .'/posts');
        $this->assertArrayNotHasKey('status', $response);

    }
}
