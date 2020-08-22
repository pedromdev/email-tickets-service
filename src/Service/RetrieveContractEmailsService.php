<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 11/08/20
 * Time: 22:52
 */

namespace App\Service;


use App\Entity\MessageReceived;
use App\Helper\ArrayHelper;
use Doctrine\ORM\EntityManagerInterface;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;
use PhpImap\Mailbox;
use SecIT\ImapBundle\Service\Imap;

class RetrieveContractEmailsService implements TicketServiceInterface
{

    /**
     * @var Imap
     */
    private $imap;

    /**
     * @var Mailbox
     */
    private $connection;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $emailRegex;

    /**
     * EmailTicket constructor.
     * @param Imap $imap
     * @param EntityManagerInterface $entityManager
     * @param string $imapUsername
     *
     * @throws \Exception
     */
    public function __construct(Imap $imap, EntityManagerInterface $entityManager, $imapUsername)
    {
        $this->imap = $imap;
        $this->entityManager = $entityManager;
        $atIndex = strpos($imapUsername, '@');
        $firstPart = substr($imapUsername, 0, $atIndex);
        $lastPart = substr($imapUsername, $atIndex);
        $this->emailRegex = "/^${firstPart}\+(?<contract_id>[a-zA-Z0-9]+){$lastPart}$/";
        $this->connection = $this->imap->get('default_connection');
    }

    public function execute(): void
    {
        $mails = $this->getUnseenMessages();

        if (!empty($mails)) {
            $mailsIds = array_column($mails, 'id');
            $messagesToPersist = array_column($mails, 'data');
            $messagesReceived = array_map(function ($messagesReceived) {
                return new MessageReceived($messagesReceived);
            }, $messagesToPersist);

            foreach ($messagesReceived as $message) {
                $this->entityManager->persist($message);
            }

            $this->entityManager->flush();

            foreach ($mailsIds as $mailId) {
                $this->connection->deleteMail($mailId);
            }
        }
    }

    /**
     * @return array
     */
    private function getUnseenMessages(): array
    {
        $mailsIds = $this->connection->searchMailbox("UNSEEN UNDELETED");

        $mailsThreads = array_reduce($this->connection->getMailsInfo($mailsIds), function ($map, $mailInfo) {
            if (isset($mailInfo->references)) {
                preg_match("/^<(?<thread_id>[^\>]+)>/", $mailInfo->references, $matches);
                $threadID = hash('sha256', "<{$matches['thread_id']}>");
            } else {
                $threadID = hash('sha256', $mailInfo->message_id);
            }
            return array_merge($map, [
                "{$mailInfo->message_id}" => $threadID
            ]);
        }, []);

        $mails = array_map(function ($mailId) {
            return $this->connection->getMail($mailId, true);
        }, $mailsIds);

        $mails = array_filter($mails, function ($mail) {
            $emails = array_keys($mail->to);
            $contractEmails = array_filter($emails, function ($email) {
                return preg_match($this->emailRegex, $email);
            });

            return !empty($contractEmails);
        });

        $mails = array_map(function (IncomingMail $mail) use ($mailsThreads) {
            $matches = [];
            $emails = array_keys($mail->to);
            ArrayHelper::find($emails, function ($email) use (&$matches) {
                return preg_match($this->emailRegex, $email, $matches);
            });

            return [
                'id' => $mail->id,
                'data' => [
                    'name' => $mail->fromName,
                    'email' => $mail->fromAddress,
                    'body' => $mail->textHtml,
                    'sent_at' => new \DateTime($mail->date),
                    'contract_id' => $matches['contract_id'],
                    'thread_id' => $mailsThreads[$mail->messageId],
                    'attachments' => array_map(function (IncomingMailAttachment $attachment) {
                        return [
                            'attachment_id' => $attachment->id,
                            'name' => $attachment->name,
                            'path' => $attachment->filePath,
                        ];
                    }, array_values($mail->getAttachments())),
                ]
            ];
        }, $mails);

        return $mails;
    }


}
