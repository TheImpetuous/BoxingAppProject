<?php

namespace TI\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TI\PlatformBundle\Entity\Advert;
use TI\PlatformBundle\Form\AdvertType;

class AdvertController extends Controller
{
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        $listAdverts = $em->getRepository('TIPlatformBundle:Advert')->findAll();

        return $this->render('TIPlatformBundle:Advert:index.html.twig', array(
            'listAdverts' => $listAdverts,
        ));
    }

    public function viewAction($id)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('TIPlatformBundle:Advert');
        $advert = $repository->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce n'existe pas!");
        }
        return $this->render('TIPlatformBundle:Advert:view.html.twig', array(
            'advert' => $advert,
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
        return $this->render('TIPlatformBundle:Advert:edit.html.twig');
    }

    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('TIPlatformBundle:Advert');
        $advert = $repository->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id " .$id. " n'existe pas!");
        }

        $em->remove($advert);
        $em->flush();

        $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée!");
        return $this->render('TIPlatformBundle:Advert:delete.html.twig');
    }

    public function menuAction()
    {
        $em = $this->getDoctrine()->getManager();
        $listAdverts = $em->getRepository('TIPlatformBundle:Advert')->findBy(
            array(),
            array('date' => 'desc'),
            3,
            0
        );
        return $this->render('TIPlatformBundle:Advert:menu.html.twig', array(
            'listAdverts' => $listAdverts,
        ));
    }
}