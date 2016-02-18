<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Genre;
use AppBundle\Entity\Movie;
use AppBundle\Entity\Type;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraints\Date;

class MovieFixtures extends AbstractFixture
{

    public function load(ObjectManager $manager)
    {

        $t1 = new Type();
        $t1->setName('Feature Film');

        $t2 = new Type();
        $t2->setName('Documentary');


        $g1 = new Genre();
        $g1->setName('thriller');

        $g2 = new Genre();
        $g2->setName('fantasy');

        $g3 = new Genre();
        $g3->setName('drama');

        $m1 = new Movie();
        $m1->setTitle('Elephant man');
        $m1->setType($t1);
        $m1->getGenres()->add($g2);

        $manager->persist($m1);

        $m2 = new Movie();
        $m2->setTitle('Ice age');
        $m2->setYear(2002);
        $m2->setReleaseDate(new \DateTime('2012-03-12'));
        $m2->setType($t2);
        $m2->getGenres()->add($g1);
        $m2->getGenres()->add($g3);

        $manager->persist($m2);

        $m3 = new Movie();
        $m3->setTitle('Deadpool');
        $m3->setType($t1);
        $m3->getGenres()->add($g1);

        $manager->persist($m3);

        $manager->flush();
    }

}
