<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;

class MovieImporter
{
    protected $em = null;

    protected $logger = null;

    public function __construct(
        EntityManager $entityManager,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->logger = $logger;

        $this->logger->info('Initializing MovieImporter');
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