<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * @Route("/levels")
     * @Method("GET")
     */
    public function levelsAction(Request $request)
    {
        $repository = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Level');

        $levels = $repository->findAll();

        return $this->render('default/levels.html.twig', array('levels' => $levels));
    }

    /**
     * @Route("/partners")
     * @Method("GET")
     */
    public function partnerssAction(Request $request)
    {
        $repository = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Partner');

        $partners = $repository->findAll();

        return $this->render('default/index.html.twig', array('partners' => $partners));
    }
}
