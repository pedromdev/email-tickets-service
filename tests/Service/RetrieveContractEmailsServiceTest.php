<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 11/08/20
 * Time: 22:53
 */

namespace App\Tests\Service;


use App\Entity\MessageReceived;
use App\Service\RetrieveContractEmailsService;
use App\Tests\BaseTestCase;
use App\Tests\Builder\MailboxMockBuilder;
use Exception;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;
use stdClass;

class RetrieveContractEmailsServiceTest extends BaseTestCase
{

    /**
     * @param IncomingMail $mail
     * @param stdClass $mailInfo
     * @dataProvider validIncomingMailProvider
     */
    public function testRetriveEmailsFromServerAndPersistInDatabase(IncomingMail $mail, stdClass $mailInfo)
    {
        $imapMock = $this
            ->getImapMockBuilder(['get'])
            ->get('default_connection', function (MailboxMockBuilder $connectionMockBuilder) use ($mail, $mailInfo) {
                $mailId = 2;
                $mailsIds = [$mailId];

                return $connectionMockBuilder
                    ->searchMailbox("UNSEEN UNDELETED", $mailsIds)
                    ->getMailsInfo($mailsIds, [$mailInfo])
                    ->getMail($mailId, true, $mail)
                    ->deleteMail($mailId)
                    ->getMock();
            })
            ->getMock();
        $entityManagerMock = $this
            ->getEntityManagerMockBuilder(['persist', 'flush'])
            ->persist(MessageReceived::class)
            ->flush()
            ->getMock();

        try {
            $service = new RetrieveContractEmailsService(
                $imapMock,
                $entityManagerMock,
                'test@example.com'
            );

            $service->execute();
        } catch (Exception $e) {
            $this->fail($e->getTraceAsString());
        }
    }

    public function validIncomingMailProvider()
    {
        return [
            [$this->getIncomingMailWithoutAttachments(), $this->getMailInfoWithoutReferences()],
            [$this->getIncomingMailWithAttachments(), $this->getMailInfoWithReferences()],
        ];
    }

    private function getIncomingMailWithoutAttachments()
    {
        $mail = new IncomingMail();

        $mail->id = 2;
        $mail->fromName = 'Test Name';
        $mail->fromAddress = 'test2@example.com';
        $mail->subject = 'Test Subject';
        $mail->date = date('Y-m-d H:i:s');
        $mail->to['test+abc123@example.com'] = 'Test';
        $mail->textHtml = '<h1>Test case</h1>';
        $mail->messageId = '<message_id1@mail.example.com>';

        return $mail;
    }

    private function getMailInfoWithoutReferences()
    {
        $mailInfo = new stdClass();

        $mailInfo->message_id = '<message_id1@mail.example.com>';

        return $mailInfo;
    }

    private function getIncomingMailWithAttachments()
    {
        $mail = new IncomingMail();
        $attachment = new IncomingMailAttachment();

        $attachment->id = 'f_test_attach';
        $attachment->name = 'file.mp4';
        $attachment->filePath = '/var/www/tmp/imap/attachments/file.mp4';

        $mail->id = 2;
        $mail->fromName = 'Test Name';
        $mail->fromAddress = 'test3@example.com';
        $mail->subject = 'Test Subject';
        $mail->date = date('Y-m-d H:i:s');
        $mail->to['test+def456@example.com'] = 'Test';
        $mail->textHtml = '<h1>Test case</h1>';
        $mail->messageId = '<message_id1@mail.example.com>';
        $mail->addAttachment($attachment);

        return $mail;
    }

    private function getMailInfoWithReferences()
    {
        $mailInfo = new stdClass();

        $mailInfo->message_id = '<message_id1@mail.example.com>';
        $mailInfo->references = '<message_id2@mail.example.com> <message_id3@mail.example.com>';

        return $mailInfo;
    }
}
