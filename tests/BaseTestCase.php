<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 11/08/20
 * Time: 23:36
 */

namespace App\Tests;


use App\Tests\Builder\EntityManagerMockBuilder;
use App\Tests\Builder\HttpClientMockBuilder;
use App\Tests\Builder\ImapMockBuilder;
use App\Tests\Builder\LoggerMockBuilder;
use App\Tests\Builder\MailboxMockBuilder;
use App\Tests\Builder\MessageReceivedRepositoryMockBuilder;
use App\Tests\Builder\ResponseMockBuilder;
use Doctrine\ORM\EntityManager;
use PhpImap\Mailbox;
use PHPUnit\Framework\TestCase;
use SecIT\ImapBundle\Service\Imap;

class BaseTestCase extends TestCase
{

    /**
     * @param array $methods
     * @param string $type
     * @return EntityManagerMockBuilder
     */
    public function getEntityManagerMockBuilder(array $methods, string $type = EntityManager::class)
    {
        return new EntityManagerMockBuilder($this, $methods, $type);
    }

    /**
     * @param array $methods
     * @param string $type
     * @return ImapMockBuilder
     */
    public function getImapMockBuilder(array $methods, string $type = Imap::class)
    {
        return new ImapMockBuilder($this, $methods, $type);
    }

    /**
     * @param array $methods
     * @param string $type
     * @return MailboxMockBuilder
     */
    public function getMailboxMockBuilder(array $methods, string $type = Mailbox::class)
    {
        return new MailboxMockBuilder($this, $methods, $type);
    }

    /**
     * @return LoggerMockBuilder
     */
    public function getLoggerMockBuilder()
    {
        return new LoggerMockBuilder($this, [
            'emergency',
            'alert',
            'critical',
            'error',
            'warning',
            'notice',
            'info',
            'debug',
            'log'
        ]);
    }

    /**
     * @param array $methods
     * @return MessageReceivedRepositoryMockBuilder
     */
    public function getMessageReceivedRepositoryMockBuilder(array $methods)
    {
        return new MessageReceivedRepositoryMockBuilder($this, $methods);
    }

    /**
     * @return HttpClientMockBuilder
     */
    public function getHttpClientMockBuilder()
    {
        return new HttpClientMockBuilder($this, [
            'stream',
            'request',
        ]);
    }

    /**
     * @return ResponseMockBuilder
     */
    public function getResponseMockBuilder()
    {
        return new ResponseMockBuilder($this, [
            'getStatusCode',
            'getHeaders',
            'toArray',
            'cancel',
            'getInfo',
            'getContent',
        ]);
    }
}
