<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 18/08/20
 * Time: 19:21
 */

namespace App\Command;


use App\Service\SendContractEmailsToEndpointService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendContractEmailsCommand extends Command
{

    protected static $defaultName = 'app:send-contract-emails';

    /**
     * @var SendContractEmailsToEndpointService
     */
    private $service;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(SendContractEmailsToEndpointService $service, LoggerInterface $logger)
    {
        parent::__construct();

        $this->service = $service;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this->setDescription('Send all unprocessed emails to the endpoint');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Sending emails to endpoint...');

        try {
            $this->service->execute();
            $output->writeln('Emails sent successfully');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }


}
