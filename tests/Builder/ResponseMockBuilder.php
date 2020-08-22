<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 19/08/20
 * Time: 20:13
 */

namespace App\Tests\Builder;


use PHPUnit\Framework\MockObject\Matcher\Invocation;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ResponseMockBuilder extends BaseMockBuilder
{

    public function __construct(TestCase $testCase, array $methods, $type = ResponseInterface::class)
    {
        parent::__construct($testCase, $methods, $type);
    }

    /**
     * @param int $statusCode
     * @param Invocation|null $matcher
     * @return $this
     */
    public function getStatusCode(int $statusCode, Invocation $matcher = null)
    {
        if (is_null($matcher)) {
            $matcher = $this->testCase->any();
        }

        $this->getMock()
            ->expects($matcher)
            ->method('getStatusCode')
            ->willReturn($statusCode);

        return $this;
    }

    /**
     * @param string $content
     * @param Invocation|null $matcher
     * @return $this
     */
    public function getContent(string $content, Invocation $matcher = null)
    {
        if (is_null($matcher)) {
            $matcher = $this->testCase->any();
        }

        $this->getMock()
            ->expects($matcher)
            ->method('getContent')
            ->willReturn($content);

        return $this;
    }
}
