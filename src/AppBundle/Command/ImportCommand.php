<?php

namespace AppBundle\Command;

use AppBundle\Entity\Genre;
use AppBundle\Entity\Movie;
use AppBundle\Entity\Type;
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
        // Getting php array of data from CSV
        // $data = $this->loadCsv($input, $output);

        $movieImporter = $this->getContainer()->get('movie_importer');
        $data = $movieImporter->convertCSVtoArray($input->getArgument('filename'), "\t");

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
        foreach ($data as $row) {

            // $movieImporter->importCSVRow();

            $movie = $em->getRepository('AppBundle:Movie')
                ->findOneBy(array('title' => $row['Title']));

            // If the movie does not exist we create one
            if (!is_object($movie)) {
                $movie = new Movie();
                $movie->setTitle($row['Title']);
            }

            // Updating info
            $movie->setYear($row['Year']);
            $movie->setRating($row['IMDb Rating']);

            $type = $em->getRepository('AppBundle:Type')
                ->findOneBy(array('name' => $row['Title type']));
            if (!is_object($type)) {
                $type = new Type();
                $type->setName($row['Title type']);
                $em->persist($type);
                $em->flush();
            }
            $movie->setType($type);


            $genres = explode(', ', $row['Genres']);
            $movie->getGenres()->clear();
            foreach ($genres as $genre) {
                $g = $em->getRepository('AppBundle:Genre')
                    ->findOneBy(array('name' => $genre));
                if (!is_object($g)) {
                    $g = new Genre();
                    $g->setName($genre);
                    $em->persist($g);
                    $em->flush(
                    ); // Important: without flush, object not written in DB, so not found with findOneBy, and we endup with duplicates
                }
                $movie->getGenres()->add($g);
            }

            // TODO handle other fields here

            // Persisting the current object
            $em->persist($movie);

            // Each 20 objects persisted we flush everything
            if (($i % $batchSize) === 0) {

                $em->flush();
                // Detaches all objects from Doctrine for memory save
                $em->clear();

                // Advancing for progress display on console
                $progress->advance($batchSize);

                $now = new \DateTime();
                $output->writeln(' of objects imported ... | '.$now->format('d-m-Y G:i:s'));
            }

            $i++;

        }

        // Flushing and clear data on queue
        $em->flush();
        $em->clear();

        // Ending the progress bar process
        $progress->finish();
    }

}