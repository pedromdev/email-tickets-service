<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 21/08/20
 * Time: 10:10
 */

namespace App\Tests\Service;


use App\Entity\MessageReceived;
use App\Service\ClearOldMessagesService;
use App\Tests\BaseTestCase;
use DateTime;

class ClearOldMessagesServiceTest extends BaseTestCase
{

    /**
     * @param MessageReceived $message
     * @dataProvider messageReceivedProvider
     */
    public function testClearOldMessagesFromDatabase(MessageReceived $message)
    {
        $repository = $this->getMessageReceivedRepositoryMockBuilder(['findMessagesFromTwoWeeksAgo'])
            ->findMessagesFromTwoWeeksAgo([$message])
            ->getMock();
        $entityManager = $this->getEntityManagerMockBuilder(['remove', 'flush'])
            ->remove(MessageReceived::class)
            ->flush()
            ->getMock();

        $service = new ClearOldMessagesService($repository, $entityManager);

        $service->execute();
    }

    public function messageReceivedProvider()
    {
        return [
            [
                new MessageReceived([
                    'id' => 3,
                    'name' => 'Test Name',
                    'email' => 'test@example.com',
                    'subject' => 'Test',
                    'body' => 'Test body',
                    'contract_id' => 'abc123',
                    'sent_at' => new DateTime(),
                    'thread_id' => '<message_id1@mail.example.com>',
                    'attachments' => []
                ])
            ],
            [
                new MessageReceived([
                    'id' => 3,
                    'name' => 'Test Name',
                    'email' => 'test@example.com',
                    'subject' => 'Test',
                    'body' => 'Test body',
                    'contract_id' => 'abc123',
                    'sent_at' => new DateTime(),
                    'thread_id' => '<message_id1@mail.example.com>',
                    'attachments' => [
                        [
                            'attachment_id' => 'f_test_attach',
                            'name' => 'attachment.mp4',
                            'path' => '/var/www/tmp/imap/attachments/attachment.txt',
                        ]
                    ]
                ])
            ],
        ];
    }
}
