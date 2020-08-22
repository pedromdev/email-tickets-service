<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 19/08/20
 * Time: 20:12
 */

namespace App\Tests\Builder;


use PHPUnit\Framework\MockObject\Matcher\Invocation;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class LoggerMockBuilder
 * @package App\Tests\Builder
 *
 * @method self error(string | array $message, Invocation $matcher = null)
 * @method self info(string | array $message, Invocation $matcher = null)
 */
class LoggerMockBuilder extends BaseMockBuilder
{

    public function __construct(TestCase $testCase, array $methods, $type = LoggerInterface::class)
    {
        parent::__construct($testCase, $methods, $type);
    }

    public function __call($name, array $arguments)
    {
        $message = array_shift($arguments);
        $matcher = array_pop($arguments);

        if (is_null($matcher)) {
            $matcher = is_array($message) ? $this->testCase->exactly(count($message)) : $this->testCase->once();
        }

        $mock = $this->getMock()
            ->expects($matcher)
            ->method($name);

        if (is_array($message)) {
            $mock->withConsecutive(
                ...array_map(function ($message) {
                    return [
                        $this->testCase->stringContains($message),
                        $this->testCase->isType('array')
                    ];
                }, $message)
            );
        } else {
            $mock->with(
                $this->testCase->stringContains($message),
                $this->testCase->isType('array')
            );
        }

        return $this;
    }


}
