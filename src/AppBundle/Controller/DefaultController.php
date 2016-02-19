<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Movie;
use AppBundle\Entity\Partner;
use AppBundle\Form\MovieType;
use AppBundle\Form\PartnerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// FIXME Should become a per page parameter one day with a dedicated select
define('NB_PER_PAGE', 25);

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
     * @Route("/partners", name="partner_list")
     * @Method("GET")
     */
    public function partnersAction(Request $request)
    {
        $repository = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Partner');

        $partners = $repository->findAll();

        return $this->render('default/index.html.twig', array('partners' => $partners));
    }

    /**
     * @Route("/partners/{id}", name="partner_view")
     * @Method("GET")
     */
    public function partnerViewAction(Request $request, $id)
    {
        $repository = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Partner');

        $partner = $repository->find($id);

        if ($partner === null) {
            throw $this->createNotFoundException('No partner found for id ' . $id);
        }

        return $this->render('default/partner.html.twig', array('partner' => $partner));
    }

    /**
     * @Route("/partner/{id}/delete", name="partner_delete")
     */
    public function partnerDeleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $partner = $em->getRepository('AppBundle:Partner')->find($id);
        if (null === $partner) {
            throw new NotFoundHttpException('No partner for id ' . $id);
        }
        $form = $this->createFormBuilder()->getForm();
        if ($form->handleRequest($request)->isValid()) {
            $em->remove($partner);
            $em->flush();
            $this->addFlash('info', 'Partner deleted');

            return $this->redirect($this->generateUrl('partner_list'));
        }

        // confirm page
        return $this->render('default/partner_delete.html.twig', array('form' => $form->createView(), 'partner' => $partner));
    }

    /**
     * @Route("/partner/{id}/edit", name="partner_edit")
     */
    public function partnerEditAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $partner = $em
            ->getRepository('AppBundle:Partner')
            ->find($id);

        if (null === $partner) {
            throw new NotFoundHttpException('No partner for id ' . $id);
        }

        $form = $this->createForm(PartnerType::class, $partner);
        // $form = $this->get('form.factory')->create(new AdvertType, $advert);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($partner);
            $em->flush();

            $this->addFlash('info', 'Partner saved');

            return $this->redirect($this->generateUrl('partner_view', array('id' => $partner->getId())));
        }

        return $this->render('default/partner_edit.html.twig', array('form' => $form->createView(), 'partner' => $partner));
    }

    /**
     * @Route("/partner/add", name="partner_add")
     */
    public function partnerAddAction(Request $request)
    {
        $partner = new Partner();

        $form = $this->createForm(PartnerType::class, $partner);
        // $form = $this->get('form.factory')->create(new AdvertType(), $advert);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($partner);
            $em->flush();

            $this->addFlash('info', 'Partner added');

            return $this->redirect($this->generateUrl('partner_view', array('id' => $partner->getId())));
        }

        return $this->render('default/partner_edit.html.twig', array('form' => $form->createView()));

    }

    /**
     * @Route("/movies/{page}", name="movie_list")
     * @Method("GET")
     */
    public function moviesAction(Request $request, $page)
    {
        $repository = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Movie');

        $movies = $repository->getAll(array(), $page, NB_PER_PAGE);

        $nbPages = ceil(count($movies) / NB_PER_PAGE);

        return $this->render('movie/movie_list.html.twig', array('movies' => $movies, 'page' => $page, 'nbPages' => $nbPages));
    }

    /**
     * @Route("/movies/{id}", name="movie_view")
     * @Method("GET")
     */
    public function movieViewAction(Request $request, $id)
    {
        $repository = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Movie');

        $movie = $repository->find($id);

        if ($movie === null) {
            throw $this->createNotFoundException('No movie found for id ' . $id);
        }

        return $this->render('movie/movie_view.html.twig', array('movie' => $movie));
    }

    /**
     * @Route("/movie/{id}/delete", name="movie_delete")
     */
    public function movieDeleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $movie = $em->getRepository('AppBundle:Movie')->find($id);
        if (null === $movie) {
            throw new NotFoundHttpException('No movie for id ' . $id);
        }
        $form = $this->createFormBuilder()->getForm();
        if ($form->handleRequest($request)->isValid()) {
            $em->remove($movie);
            $em->flush();
            $this->addFlash('info', 'Movie deleted');

            return $this->redirect($this->generateUrl('movie_list'));
        }

        // confirm page
        return $this->render('movie/movie_delete.html.twig', array('form' => $form->createView(), 'movie' => $movie));
    }

    /**
     * @Route("/movie/{id}/edit", name="movie_edit")
     */
    public function movieEditAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $movie = $em
            ->getRepository('AppBundle:Movie')
            ->find($id);

        if (null === $movie) {
            throw new NotFoundHttpException('No movie for id ' . $id);
        }

        $form = $this->createForm(MovieType::class, $movie);
        // $form = $this->get('form.factory')->create(new AdvertType, $advert);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($movie);
            $em->flush();

            $this->addFlash('info', 'Movie saved');

            return $this->redirect($this->generateUrl('movie_view', array('id' => $movie->getId())));
        }

        return $this->render('movie/movie_edit.html.twig', array('form' => $form->createView(), 'movie' => $movie));
    }

    /**
     * @Route("/movie/add", name="movie_add")
     */
    public function movieAddAction(Request $request)
    {
        $movie = new Movie();

        $form = $this->createForm(MovieType::class, $movie);
        // $form = $this->get('form.factory')->create(new AdvertType(), $advert);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($movie);
            $em->flush();

            $this->addFlash('info', 'Movie added');

            return $this->redirect($this->generateUrl('movie_view', array('id' => $movie->getId())));
        }

        return $this->render('movie/movie_edit.html.twig', array('form' => $form->createView()));

    }    
}
