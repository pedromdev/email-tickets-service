<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 18/08/20
 * Time: 19:20
 */

namespace App\Service;


use App\Entity\MessageReceived;
use App\Repository\MessageReceivedRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SendContractEmailsToEndpointService implements TicketServiceInterface
{

    /**
     * @var MessageReceivedRepository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var int
     */
    private $maxAttempts;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * SendContractEmailsToEndpointService constructor.
     * @param MessageReceivedRepository $repository
     * @param LoggerInterface $appLogger
     * @param string $endpoint
     * @param int $maxAttempts
     * @param HttpClientInterface $client
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        MessageReceivedRepository $repository,
        LoggerInterface $appLogger,
        string $endpoint,
        int $maxAttempts,
        HttpClientInterface $client,
        EntityManagerInterface $entityManager
    )
    {
        $this->repository = $repository;
        $this->logger = $appLogger;
        $this->client = $client;
        $this->endpoint = $endpoint;
        $this->maxAttempts = $maxAttempts;
        $this->entityManager = $entityManager;
    }


    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function execute(): void
    {
        $receivedMessages = $this->repository->findBy(
            ['processed_at' => null],
            ['sent_at' => 'ASC']
        );

        /** @var MessageReceived $message */
        foreach ($receivedMessages as $message) {
            $processed = $this->tryToSendToEndpoint($message);

            if ($processed) {
                $message->setProcessedAt(new DateTime());
                $this->entityManager->persist($message);
                $this->entityManager->flush();
            } else {
                $this->logError(
                    "Envio falhou após o número máximo de tentativas. Limite de tentativa: %d",
                    $this->maxAttempts
                );
            }
        }
    }

    /**
     * @param MessageReceived $message
     * @return bool
     */
    private function tryToSendToEndpoint(MessageReceived $message): bool
    {
        $attempt = 1;
        $processed = false;

        do {
            $statusCode = 0;

            try {
                $formData = $message->toFormDataPart();
                $response = $this->client->request('POST', $this->endpoint, [
                    'headers' => $formData->getPreparedHeaders()->toArray(),
                    'body' => $formData->bodyToIterable(),
                ]);
                $statusCode = $response->getStatusCode();
                $content = $response->getContent();

                if ($statusCode == 200) {
                    $processed = true;
                    $this->logInfo(
                        "Tentativa de envio #%d da mensagem #%d foi um sucesso.\nConteúdo da resposta: %s",
                        $attempt,
                        $message->getId(),
                        $content
                    );
                }
            } catch (TransportExceptionInterface $e) {
                $this->logError(
                    "Tentativa de envio #%d da mensagem #%d falhou: %s",
                    $attempt,
                    $message->getId(),
                    $e->getTraceAsString()
                );
            } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
                $this->logErrorAttempt($attempt, $message, $e);
            }

        } while (++$attempt <= $this->maxAttempts && $statusCode !== 200);

        return $processed;
    }

    private function logInfo()
    {
        $this->logger->info(call_user_func_array('sprintf', func_get_args()));
    }

    private function logError()
    {
        $this->logger->error(call_user_func_array('sprintf', func_get_args()));
    }

    /**
     * @param int $attempt
     * @param MessageReceived $message
     * @param HttpExceptionInterface $e
     */
    private function logErrorAttempt(int $attempt, MessageReceived $message, HttpExceptionInterface $e): void
    {
        try {
            $this->logError(
                "Tentativa de envio #%d da mensagem #%d falhou.\nStatus da resposta: %d\nConteúdo da resposta: %s\nExceção: %s",
                $attempt,
                $message->getId(),
                $e->getResponse()->getStatusCode(),
                $e->getResponse()->getContent(false),
                $e->getTraceAsString()
            );
        } catch (TransportExceptionInterface $e) {
            $this->logError(
                "Erro ao tentar gravar log de falha de tentativa: %s",
                $e->getTraceAsString()
            );
        }
    }
}
