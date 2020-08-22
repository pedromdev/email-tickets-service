<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 18/08/20
 * Time: 19:21
 */

namespace App\Tests\Service;


use App\Entity\MessageReceived;
use App\Service\SendContractEmailsToEndpointService;
use App\Tests\BaseTestCase;
use App\Tests\Builder\ResponseMockBuilder;
use DateTime;
use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseStack;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class SendContractEmailsToEndpointServiceTest extends BaseTestCase
{

    private const ATTACHMENT_PATH = __DIR__ . '/attachment.txt';
    /**
     * @var MockWebServer
     */
    protected static $server;
    /**
     * @var FileSystem
     */
    private $filesystem;

    public static function setUpBeforeClass()
    {
        self::$server = new MockWebServer;
        self::$server->start();
    }

    public static function tearDownAfterClass()
    {
        self::$server->stop();
    }

    /**
     * @param MessageReceived $message
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @dataProvider messageReceivedProvider
     */
    public function testSendUnprocessedEmailToEndpoint(MessageReceived $message)
    {
        $repository = $this->getMessageReceivedRepositoryMockBuilder(['findBy'])
            ->findBy(['processed_at' => null], [$message], ['sent_at' => 'ASC'])
            ->getMock();
        $logger = $this->getLoggerMockBuilder()
            ->info("Tentativa de envio #1 da mensagem #3 foi um sucesso.\nConteúdo da resposta: Ok")
            ->getMock();
        $endpoint = 'http://test.com/endpoint';
        $maxAttempts = 5;
        $client = $this->getHttpClientMockBuilder()
            ->request('POST', $endpoint, function (ResponseMockBuilder $response) {
                return $response
                    ->getStatusCode(200)
                    ->getContent('Ok')
                    ->getMock();
            })
            ->getMock();
        $entityManager = $this->getEntityManagerMockBuilder(['persist', 'flush'])
            ->persist(MessageReceived::class)
            ->flush()
            ->getMock();

        $service = new SendContractEmailsToEndpointService(
            $repository,
            $logger,
            $endpoint,
            $maxAttempts,
            $client,
            $entityManager
        );

        $service->execute();

        $this->assertInstanceOf(DateTime::class, $message->getProcessedAt());
    }

    /**
     * @param MessageReceived $message
     * @param string $exceptionClass
     * @param int $statusCode
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @dataProvider messagesWithExceptions
     */
    public function testTryAllAttemptsToSendTheMessagesToEndpoint(MessageReceived $message, string $exceptionClass, int $statusCode)
    {
        $endpoint = self::$server->setResponseOfPath('/endpoint', new ResponseStack(
            new Response('Error', [], $statusCode),
            new Response('Error', [], $statusCode),
            new Response('Error', [], $statusCode),
            new Response('Error', [], $statusCode),
            new Response('Error', [], $statusCode)
        ));
        $repository = $this->getMessageReceivedRepositoryMockBuilder(['findBy'])
            ->findBy(['processed_at' => null], [$message], ['sent_at' => 'ASC'])
            ->getMock();
        $logger = $this->getLoggerMockBuilder()
            ->error([
                "Tentativa de envio #1 da mensagem #3 falhou.\nStatus da resposta: {$statusCode}\nConteúdo da resposta: Error",
                "Tentativa de envio #2 da mensagem #3 falhou.\nStatus da resposta: {$statusCode}\nConteúdo da resposta: Error",
                "Tentativa de envio #3 da mensagem #3 falhou.\nStatus da resposta: {$statusCode}\nConteúdo da resposta: Error",
                "Tentativa de envio #4 da mensagem #3 falhou.\nStatus da resposta: {$statusCode}\nConteúdo da resposta: Error",
                "Tentativa de envio #5 da mensagem #3 falhou.\nStatus da resposta: {$statusCode}\nConteúdo da resposta: Error",
                'Envio falhou após o número máximo de tentativas. Limite de tentativa: 5'
            ])
            ->getMock();
        $maxAttempts = 5;
        $entityManager = $this->getEntityManagerMockBuilder([])->getMock();

        $service = new SendContractEmailsToEndpointService(
            $repository,
            $logger,
            $endpoint,
            $maxAttempts,
            new CurlHttpClient(),
            $entityManager
        );

        $service->execute();

        $this->assertNull($message->getProcessedAt());
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
                            'path' => self::ATTACHMENT_PATH,
                        ]
                    ]
                ])
            ],
        ];
    }

    public function messagesWithExceptions()
    {
        $message = new MessageReceived([
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
                    'path' => self::ATTACHMENT_PATH,
                ]
            ]
        ]);

        return [
            [$message, RedirectionExceptionInterface::class, 300],
            [$message, ClientExceptionInterface::class, 400],
            [$message, ServerExceptionInterface::class, 500],
        ];
    }

    protected function setUp()
    {
        $this->filesystem = new Filesystem();
        $this->filesystem->touch(self::ATTACHMENT_PATH);
    }

    protected function tearDown()
    {
        $this->filesystem->remove(self::ATTACHMENT_PATH);
    }
}
