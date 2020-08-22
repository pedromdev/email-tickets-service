<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 20/08/20
 * Time: 19:45
 */

namespace App\Command;


use App\Service\TicketServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearOldMessagesCommand extends Command
{

    protected static $defaultName = 'app:clear-old-messages';

    /**
     * @var TicketServiceInterface
     */
    private $service;

    public function __construct()
    {
        parent::__construct();
    }


    protected function configure()
    {
        $this->setDescription('Clear processed messages from 2 weeks ago');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Removing old messages from database...');

        try {
            $this->service->execute();
            $output->writeln('Messages removed successfully');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }


}
