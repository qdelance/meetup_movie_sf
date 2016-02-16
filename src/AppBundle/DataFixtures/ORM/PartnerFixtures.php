<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Partner;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PartnerFixtures extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {

        $p1 = new Partner();
        $p1->setName('Silver Partner');
        $p1->setAddress("Address 1\nLong line\nOne more line");
        // Somewhat ugly but ...
        $p1->setLevel($this->getReference('level-Silver'));

        $manager->persist($p1);

        $p2 = new Partner();
        $p2->setName('Very Good Partner');
        $p2->setAddress("Address 2\nLong line\nOne more line");
        // Somewhat ugly but ...
        $p2->setLevel($this->getReference('level-Gold'));

        $manager->persist($p2);

        $p3 = new Partner();
        $p3->setName('One More Partner');
        $p3->setAddress("Lorem ipsum...");

        $manager->persist($p3);

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 2;
    }
}
