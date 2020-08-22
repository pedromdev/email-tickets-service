<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 11/08/20
 * Time: 23:48
 */

namespace App\Tests\Builder;


use PhpImap\Mailbox;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SecIT\ImapBundle\Service\Imap;

class ImapMockBuilder extends BaseMockBuilder
{

    public function __construct(TestCase $testCase, array $methods, $type = Imap::class)
    {
        parent::__construct($testCase, $methods, $type);
    }

    /**
     * @param string $name
     * @param Mailbox|MockObject|Callable $connection
     * @param Invocation|null $matcher
     * @return $this
     */
    public function get(string $name, $connection, Invocation $matcher = null)
    {
        $return = null;

        if (is_null($matcher)) {
            $matcher = $this->testCase->once();
        }

        if ($connection instanceof Mailbox || $connection instanceof MockObject) {
            $return = $connection;
        } else if (is_callable($connection)) {
            $return = call_user_func($connection, new MailboxMockBuilder($this->testCase, [
                'searchMailbox',
                'getMailsInfo',
                'getMail',
                'deleteMail',
            ]));
        }

        $this->getMock()->expects($matcher)
            ->method('get')
            ->with($this->testCase->equalTo($name))
            ->willReturn($return);

        return $this;
    }
}
