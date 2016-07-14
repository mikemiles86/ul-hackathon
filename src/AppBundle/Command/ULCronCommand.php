<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ULCronCommand extends ContainerAwareCommand
{
  protected function configure()
  {
    $this
      ->setName('ulcron:run')
      ->setDescription('Runs UL cron tasks.')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $taskrunner = $this->getContainer()->get('app.ultaskrunner');
    $update_count = $taskrunner->updateContentDocuments();
    $output->writeln($update_count . ' documents updated.');
    // @TODO: Add argument for allowed time.
  }
}
