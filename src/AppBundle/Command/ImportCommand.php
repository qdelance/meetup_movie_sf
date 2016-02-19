<?php

namespace AppBundle\Command;

use AppBundle\Entity\Movie;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Showing when the script is launched
        $now = new \DateTime();
        $output->writeln('<comment>Start : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');

        // Importing CSV on DB via Doctrine ORM
        $this->import($input, $output);

        // Showing when the script is over
        $now = new \DateTime();
        $output->writeln('<comment>End : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');

    }

    protected function import(InputInterface $input, OutputInterface $output)
    {
        // Getting php array of data from CSV
        $data = $this->loadCsv($input, $output);

        // Getting doctrine manager
        $em = $this->getContainer()->get('doctrine')->getManager();
        // Turning off doctrine default logs queries for saving memory
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        // Define the size of record, the frequency for persisting the data and the current index of records
        $size = count($data);
        $batchSize = 20;
        $i = 1;

        // Starting progress
        $progress = new ProgressBar($output, $size);
        $progress->start();

        // Processing on each row of data
        foreach($data as $row) {

            $movie = $em->getRepository('AppBundle:Movie')
                ->findOneBy(array('title' => $row[5]));

            // If the movie does not exist we create one
            if(!is_object($movie)){
                $movie = new Movie();
                $movie->setTitle($row[5]);
            }

            // Updating info
            $movie->setYear($row[11]);
            $movie->setRating($row[9]);

            // TODO handle other fields here

            // Persisting the current user
            $em->persist($movie);

            // Each 20 objects persisted we flush everything
            if (($i % $batchSize) === 0) {

                $em->flush();
                // Detaches all objects from Doctrine for memory save
                $em->clear();

                // Advancing for progress display on console
                $progress->advance($batchSize);

                $now = new \DateTime();
                $output->writeln(' of objects imported ... | ' . $now->format('d-m-Y G:i:s'));

            }

            $i++;

        }

        // Flushing and clear data on queue
        $em->flush();
        $em->clear();

        // Ending the progress bar process
        $progress->finish();
    }

    protected function loadCsv(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        $logger = $this->getContainer()->get('logger');

        $logger->debug('Reading ' . $filename);

        $row = 0;
        $result = array();
        if (($handle = fopen($filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
                $num = count($data);
                $logger->debug("$num fields on line $row");
                $result[$row] = $data;
                $row++;
            }
            fclose($handle);
        }

        $logger->debug('File read, nb of rows : ' . $row);

        return $result;
    }
}