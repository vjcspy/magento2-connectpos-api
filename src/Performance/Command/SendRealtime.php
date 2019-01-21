<?php

namespace SM\Performance\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SendRealtime
 *
 * @package SM\Performance\Command
 */
class SendRealtime extends Command {

    /**
     * @var \SM\Performance\Helper\RealtimeManager
     */
    private $realtimeManager;

    public function __construct(
        \SM\Performance\Helper\RealtimeManager $realtimeManager,
        $name = null
    ) {
        $this->realtimeManager = $realtimeManager;
        parent::__construct($name);
    }

    protected function configure() {
        $this->setName("retail:sendrealtime");
        $this->setDescription("Realtime sync command for PHP async task");
        $this->addArgument('data', \Symfony\Component\Console\Input\InputArgument::REQUIRED, "json data to send server");
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $data = $input->getArgument('data');
        if (!is_null($data) && is_string($data)) {
            $data = json_decode($data, true);
            if (is_array($data)) {
                $this->realtimeManager->getSenderInstance()->sendMessages($data);
                $output->writeln('<info>sent_data</info>');
            }
            else {
                $output->writeln('<error>data_wrong_format</error>');
            }
        }
        else {
            $output->writeln('<error>data_wrong_format</error>');
        }
    }
} 