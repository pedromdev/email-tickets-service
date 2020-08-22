<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 19/08/20
 * Time: 20:12
 */

namespace App\Tests\Builder;


use App\Entity\MessageReceived;
use App\Helper\ArrayHelper;
use App\Repository\MessageReceivedRepository;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
use PHPUnit\Framework\TestCase;

class MessageReceivedRepositoryMockBuilder extends BaseMockBuilder
{

    public function __construct(TestCase $testCase, array $methods)
    {
        parent::__construct($testCase, $methods, MessageReceivedRepository::class);
    }

    /**
     * @param array $criteria
     * @param MessageReceived[] $returnedMessages
     * @param array|null $orderBy
     * @param Invocation|null $matcher
     * @return $this
     */
    public function findBy(array $criteria, array $returnedMessages, array $orderBy = null, Invocation $matcher = null)
    {
        if (is_null($matcher)) {
            $matcher = $this->testCase->once();
        }

        $this->getMock()
            ->expects($matcher)
            ->method('findBy')
            ->with(
                $this->testCase->callback(function ($arg) use ($criteria) {
                    return $arg == $criteria || ArrayHelper::equals($arg, $criteria);
                }),
                $this->testCase->callback(function ($arg) use ($orderBy) {
                    return $arg == $orderBy || ArrayHelper::equals($arg, $orderBy);
                })
            )
            ->willReturn($returnedMessages);

        return $this;
    }

    /**
     * @param array $returnedMessages
     * @param Invocation|null $matcher
     * @return $this
     */
    public function findMessagesFromTwoWeeksAgo(array $returnedMessages, Invocation $matcher = null)
    {
        if (is_null($matcher)) {
            $matcher = $this->testCase->once();
        }

        $this->getMock()
            ->expects($matcher)
            ->method('findMessagesFromTwoWeeksAgo')
            ->willReturn($returnedMessages);

        return $this;
    }

}
