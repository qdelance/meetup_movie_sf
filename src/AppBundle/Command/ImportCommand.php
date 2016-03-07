<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('movie:import')
            ->setDescription('Import movies using CSV file')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'CSV file to import?'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Showing when the script is launched
        $now = new \DateTime();
        $output->writeln('<comment>Start : '.$now->format('d-m-Y G:i:s').' ---</comment>');

        // Importing CSV on DB via Doctrine ORM
        $this->import($input, $output);

        // Showing when the script is over
        $now = new \DateTime();
        $output->writeln('<comment>End : '.$now->format('d-m-Y G:i:s').' ---</comment>');

    }

    protected function import(InputInterface $input, OutputInterface $output)
    {
        $movieImporter = $this->getContainer()->get('movie_importer');

        // Loading CSV
        $data = $movieImporter->convertCSVtoArray($input->getArgument('filename'), "\t");

        $dispatcher = $this->getContainer()->get('event_dispatcher');

        // Progress, based on the number of lines from the CSV
        $progress = new ProgressBar($output, count($data));
        $progress->start();

        $dispatcher->addListener(
            'movie_importer.progress',
            function (GenericEvent $event) use ($output, $progress) {
                $progress->advance($event->getArgument('advance'));
                $now = new \DateTime();
                $output->writeln(' of objects imported ... | '.$now->format('d-m-Y G:i:s'));
            }
        );

        // Real work happens here
        $movieImporter->importArray($data);

        $progress->finish();
        $output->writeln('');
    }
}