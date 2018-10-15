<?php

namespace TI\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TI\PlatformBundle\Entity\Advert;
use TI\PlatformBundle\Entity\Application;
use TI\PlatformBundle\Form\AdvertEditType;
use TI\PlatformBundle\Form\AdvertType;
use TI\PlatformBundle\Form\ApplicationType;

class AdvertController extends Controller
{
    public function indexAction($page)
    {
        if ($page == null) {
            $page = 1;
        }
        if ($page < 1) {
            throw $this->createNotFoundException("La page " . $page . " n'existe pas.");
        }

        $nbPerPage = 5;

        $listAdverts = $this->getDoctrine()->getManager()
            ->getRepository('TIPlatformBundle:Advert')->getAdverts($page, $nbPerPage);

        $nbPages = ceil(count($listAdverts) / $nbPerPage);

        if ($page > $nbPages) {
            $this->createNotFoundException("La page " . $page . " n'existe pas!");
        }

        $nbAdverts = $this->getDoctrine()->getManager()
            ->getRepository('TIPlatformBundle:Advert')
            ->getNbAdverts();

        return $this->render('TIPlatformBundle:Advert:index.html.twig', array(
            'listAdverts' => $listAdverts,
            'nbPages' => $nbPages,
            'page' => $page,
            'nbAdverts' => $nbAdverts,
        ));
    }

    public function viewAction(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('TIPlatformBundle:Advert');
        $advert = $repository->getAdvertWithAllStuff($id);

        $application = new Application();
        $form = $this->createForm(ApplicationType::class, $application);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $advert->addApplication($application);
            $em->persist($application);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', 'Candidature ajoutée!');

            $this->redirectToRoute('ti_platform_view', array(
                'id' => $advert->getId(),
            ));
        }

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce n'existe pas!");
        }

        return $this->render('TIPlatformBundle:Advert:view.html.twig', array(
            'advert' => $advert,
            'form' => $form->createView(),
        ));
    }

    public function addAction(Request $request)
    {
        $advert = new Advert();
        $form = $this->createForm(AdvertType::class, $advert);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();

            return $this->redirectToRoute('ti_platform_view', array(
                'id' => $advert->getId(),
            ));
        }
        return $this->render('TIPlatformBundle:Advert:add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editAction(Request $request, $id)
    {
        $advert = $this->getDoctrine()->getManager()->getRepository('TIPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas!");
        }
        $form = $this->createForm(AdvertEditType::class, $advert);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', 'Annonce modifiée');

            return $this->redirectToRoute('ti_platform_view', array(
                'id' => $advert->getId(),
            ));
        }

        return $this->render('TIPlatformBundle:Advert:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('TIPlatformBundle:Advert');
        $advert = $repository->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas!");
        }

        $em->remove($advert);
        $em->flush();

        $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée!");
        return $this->redirectToRoute('ti_platform_home');
    }

    public function menuAction($limit)
    {
        $em = $this->getDoctrine()->getManager();
        $listAdverts = $em->getRepository('TIPlatformBundle:Advert')->findBy(
            array(),
            array('date' => 'desc'),
            $limit,
            0
        );
        return $this->render('TIPlatformBundle:Advert:menu.html.twig', array(
            'listAdverts' => $listAdverts,
        ));
    }

    public function deleteApplicationAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $application = $em->getRepository('TIPlatformBundle:Application')->find($id);

        if (null === $application) {
            throw new NotFoundHttpException("La candidature n'existe pas");
        }

        $advertId = $application->getAdvert()->getId();

        $em->remove($application);
        $em->flush();

        $request->getSession()->getFlashBag()->add('info', 'La candidature a bien été supprimée!');

        return $this->redirectToRoute('ti_platform_view', array(
            'id' => $advertId,
        ));
    }
}