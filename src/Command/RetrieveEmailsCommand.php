<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 12/08/20
 * Time: 00:12
 */

namespace App\Command;


use App\Service\RetrieveContractEmailsService;
use App\Service\TicketServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RetrieveEmailsCommand extends Command
{

    protected static $defaultName = 'app:retrieve-emails';

    /**
     * @var TicketServiceInterface
     */
    private $service;

    public function __construct(RetrieveContractEmailsService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    protected function configure()
    {
        $this->setDescription('Retrieve emails from server and persist to database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Retrieving emails from server...');

        try {
            $this->service->execute();
            $output->writeln('Emails persisted successfully');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }


}
