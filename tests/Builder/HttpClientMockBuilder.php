<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 19/08/20
 * Time: 20:13
 */

namespace App\Tests\Builder;


use PHPUnit\Framework\MockObject\Matcher\Invocation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpClientMockBuilder extends BaseMockBuilder
{

    public function __construct(TestCase $testCase, array $methods, $type = HttpClientInterface::class)
    {
        parent::__construct($testCase, $methods, $type);
    }

    /**
     * @param string $method
     * @param string $url
     * @param ResponseInterface|MockObject|Callable $response
     * @param Invocation|null $matcher
     * @return $this
     */
    public function request(string $method, string $url, $response, Invocation $matcher = null)
    {
        $return = null;

        if (is_null($matcher)) {
            $matcher = $this->testCase->once();
        }

        if ($response instanceof ResponseInterface || $response instanceof MockObject) {
            $return = $response;
        } else {
            $return = call_user_func($response, new ResponseMockBuilder($this->testCase, [
                'getStatusCode',
                'getHeaders',
                'toArray',
                'cancel',
                'getInfo',
                'getContent',
            ]));
        }

        $this->getMock()
            ->expects($matcher)
            ->method('request')
            ->with(
                $this->testCase->equalTo($method),
                $this->testCase->equalTo($url),
                $this->testCase->isType('array')
            )
            ->willReturn($return);
        return $this;
    }

    /**
     * @param string $method
     * @param string $url
     * @param ExceptionInterface $exception
     * @param Invocation|null $matcher
     * @return $this
     */
    public function requestThrowsException(string $method, string $url, ExceptionInterface $exception, Invocation $matcher = null)
    {
        $return = null;

        if (is_null($matcher)) {
            $matcher = $this->testCase->once();
        }

        $this->getMock()
            ->expects($matcher)
            ->method('request')
            ->with(
                $this->testCase->equalTo($method),
                $this->testCase->equalTo($url),
                $this->testCase->isType('array')
            )
            ->willThrowException($exception);
        return $this;
    }
}
