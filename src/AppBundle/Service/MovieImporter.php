<?php

namespace AppBundle\Service;

use AppBundle\Entity\Genre;
use AppBundle\Entity\Movie;
use AppBundle\Entity\Type;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class MovieImporter
{
    protected $em = null;

    protected $ed = null;

    protected $logger = null;

    public function __construct(
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->ed = $eventDispatcher;
        $this->logger = $logger;

        $this->logger->info('Initializing MovieImporter');
    }

    public function importArray(array $data) {

        // Turning off doctrine default logs queries for saving memory
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        // Process items 20 by 20
        $batchSize = 100;
        $i = 1;

        // Processing on each row of data
        foreach ($data as $row) {

            // $movieImporter->importCSVRow();

            $movie = $this->em->getRepository('AppBundle:Movie')
                ->findOneBy(array('title' => $row['Title']));

            // If the movie does not exist we create one
            if (!is_object($movie)) {
                $movie = new Movie();
                $movie->setTitle($row['Title']);
            }

            // Updating info
            $movie->setYear($row['Year']);
            $movie->setRating($row['IMDb Rating']);

            $type = $this->em->getRepository('AppBundle:Type')
                ->findOneBy(array('name' => $row['Title type']));
            if (!is_object($type)) {
                $type = new Type();
                $type->setName($row['Title type']);
                $this->em->persist($type);
                $this->em->flush();
            }
            $movie->setType($type);


            $genres = explode(', ', $row['Genres']);
            $movie->getGenres()->clear();
            foreach ($genres as $genre) {
                $g = $this->em->getRepository('AppBundle:Genre')
                    ->findOneBy(array('name' => $genre));
                if (!is_object($g)) {
                    $g = new Genre();
                    $g->setName($genre);
                    $this->em->persist($g);
                    $this->em->flush(
                    ); // Important: without flush, object not written in DB, so not found with findOneBy, and we endup with duplicates
                }
                $movie->getGenres()->add($g);
            }

            // TODO handle other fields here

            // Persisting the current object
            $this->em->persist($movie);

            // Each 20 objects persisted we flush everything
            if (($i % $batchSize) === 0) {

                $this->em->flush();
                // Detaches all objects from Doctrine for memory save
                $this->em->clear();

                // Notify progress
                $this->ed->dispatch(
                    'movie_importer.progress',
                    new GenericEvent('progress', array('advance' => $batchSize))
                );
            }

            $i++;

        }

        // Flushing and clear data on queue
        $this->em->flush();
        $this->em->clear();

    }

    public function convertCSVtoArray($filename, $delimiter = ',')
    {
        $this->logger->info('Reading file '.$filename);
        if (!file_exists($filename) || !is_readable($filename)) {
            $this->logger->error('Error reading file '.$filename);
            return false;
        }

        $header = null;
        $data = array();

        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }
}