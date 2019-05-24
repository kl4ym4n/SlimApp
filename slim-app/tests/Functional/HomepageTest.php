<?php

namespace Tests\Functional;

class HomepageTest extends BaseTestCase
{
    /**
     * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
     */

    public function testLoginPage()
    {
        $response = $this->runApp('GET', '/user/login');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsStringIgnoringCase('Login', (string)$response->getBody());
    }

    public function testLogin()
    {
        $response = $this->runApp('POST', '/user/login', [ 'username' => '123', 'password'=> '123']);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testNotExistLogin()
    {
        $response = $this->runApp('POST', '/user/login', [ 'username' => '12ytyty', 'password'=> '123']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsStringIgnoringCase('Login', (string)$response->getBody());
    }

    public function testSignupPage()
    {
        $response = $this->runApp('GET', '/user/signup');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsStringIgnoringCase('Signup', (string)$response->getBody());
    }

    public function testFailPassConfirmSignup()
    {
        $response = $this->runApp('POST', '/user/signup', [ 'first_name' => 'Test',
            'mobile_phone' => '123455', 'email' => 'aaa2@mail.com', 'password' => '123']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsStringIgnoringCase('Password Confirmation is required', (string)$response->getBody());
    }

    public function testFailNameSignup()
    {
        $response = $this->runApp('POST', '/user/signup', [ 'mobile_phone' => '123455',
            'email' => 'aaa2@mail.com', 'password' => '123', 'password_confirmation' => '123']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsStringIgnoringCase('Name is required', (string)$response->getBody());
    }

    public function testFailPhoneSignup()
    {
        $response = $this->runApp('POST', '/user/signup', [ 'first_name' => 'Test',
            'email' => 'aaa2@mail.com', 'password' => '123', 'password_confirmation' => '123']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsStringIgnoringCase('Phone is required', (string)$response->getBody());
    }

    public function testFailEmailSignup()
    {
        $response = $this->runApp('POST', '/user/signup', [ 'first_name' => 'Test',
            'mobile_phone' => '123455', 'password' => '123', 'password_confirmation' => '123']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsStringIgnoringCase('Email is required', (string)$response->getBody());
    }

    public function testFailPasswordSignup()
    {
        $response = $this->runApp('POST', '/user/signup', [ 'first_name' => 'Test', 'email' => 'aaa2@mail.com',
            'mobile_phone' => '123455',  'password_confirmation' => '123']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsStringIgnoringCase('Password is required', (string)$response->getBody());
    }

    public function testSignup()
    {
        $chars = '0123456789';
        $phone = '';
        for ($i = 0; $i < 10; $i++) {
            $ind = rand(0, strlen($chars) - 1);
            $phone .= $chars[$ind];
        }
        $email = substr(md5(mt_rand()), 0, 7) . '@mail.com';
        $user_data = [ 'first_name' => 'Test', 'mobile_phone' => $phone, 'email' => $email, 'password' => '123',
            'password_confirmation' => '123' ];
        $response = $this->runApp('POST', '/user/signup', $user_data);
        $this->assertEquals(302, $response->getStatusCode());
    }
}
