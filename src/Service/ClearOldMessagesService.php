<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 20/08/20
 * Time: 19:46
 */

namespace App\Service;


use App\Repository\MessageReceivedRepository;
use Doctrine\ORM\EntityManagerInterface;

class ClearOldMessagesService implements TicketServiceInterface
{

    /**
     * @var MessageReceivedRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ClearOldMessagesService constructor.
     * @param MessageReceivedRepository $repository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(MessageReceivedRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }


    public function execute(): void
    {
        $messages = $this->repository->findMessagesFromTwoWeeksAgo();

        foreach ($messages as $message) {
            $this->entityManager->remove($message);
        }

        $this->entityManager->flush();
    }
}
