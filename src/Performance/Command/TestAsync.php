<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 10/3/17
 * Time: 2:13 PM
 */

namespace SM\Performance\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestAsync extends Command {

    public function __construct(
        $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure() {
        $this->setName("retail:testasync");
        $this->setDescription("Test PHP command");
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('<info>ok</info>');
    }
}