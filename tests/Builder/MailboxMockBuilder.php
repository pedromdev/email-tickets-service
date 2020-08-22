<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 11/08/20
 * Time: 23:48
 */

namespace App\Tests\Builder;


use PhpImap\IncomingMail;
use PhpImap\Mailbox;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
use PHPUnit\Framework\TestCase;

class MailboxMockBuilder extends BaseMockBuilder
{

    public function __construct(TestCase $testCase, array $methods, $type = Mailbox::class)
    {
        parent::__construct($testCase, $methods, $type);
    }

    /**
     * @param string $criteria
     * @param array $returnedMailsIds
     * @param Invocation $matcher
     * @return $this
     */
    public function searchMailbox(string $criteria, array $returnedMailsIds, Invocation $matcher = null)
    {
        if (is_null($matcher)) {
            $matcher = $this->testCase->once();
        }

        $this->getMock()->expects($matcher)
            ->method('searchMailbox')
            ->with($this->testCase->equalTo($criteria))
            ->willReturn($returnedMailsIds);

        return $this;
    }

    /**
     * @param array $mailsIds
     * @param array $returnedMailsInfo
     * @param Invocation $matcher
     * @return $this
     */
    public function getMailsInfo(array $mailsIds, array $returnedMailsInfo, Invocation $matcher = null)
    {
        if (is_null($matcher)) {
            $matcher = $this->testCase->once();
        }

        $this->getMock()->expects($matcher)
            ->method('getMailsInfo')
            ->with($this->testCase->equalTo($mailsIds))
            ->willReturn($returnedMailsInfo);

        return $this;
    }

    /**
     * @param int $mailId
     * @param bool $markAsSeen
     * @param IncomingMail $mail
     * @param Invocation $matcher
     * @return $this
     */
    public function getMail(int $mailId, bool $markAsSeen, IncomingMail $mail, Invocation $matcher = null)
    {
        if (is_null($matcher)) {
            $matcher = $this->testCase->once();
        }

        $this->getMock()->expects($matcher)
            ->method('getMail')
            ->with(
                $this->testCase->equalTo($mailId),
                $this->testCase->equalTo($markAsSeen)
            )
            ->willReturn($mail);

        return $this;

    }

    public function deleteMail(int $mailId, Invocation $matcher = null)
    {
        if (is_null($matcher)) {
            $matcher = $this->testCase->once();
        }

        $this->getMock()->expects($matcher)
            ->method('deleteMail')
            ->with($this->testCase->equalTo($mailId));

        return $this;

    }

}
