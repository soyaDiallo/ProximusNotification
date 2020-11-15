<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Raison;
use App\Entity\Remuneration;
use App\Form\CategorieType;
use App\Form\FournisseurType;
use App\Form\RaisonType;
use App\Form\RemunerationType;
use App\Repository\CategorieRepository;
use App\Repository\FournisseurRepository;
use App\Repository\RaisonRepository;
use App\Repository\RemunerationRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/autres")
 * @Security("is_granted('ROLE_ADMINISTRATEUR')")
 */
class AutreController extends AbstractController
{
    /**
     * @Route("/", name="autre_index", methods={"GET", "POST"})
     */
    public function index(Request $request, RaisonRepository $raisonRepository, RemunerationRepository $remunerationRepository): Response
    {
        $raison = new Raison();
        $form = $this->createForm(RaisonType::class, $raison);
        $form->handleRequest($request);

        $remuneration = new Remuneration();
        $formRemuneration = $this->createForm(RemunerationType::class, $remuneration);
        $formRemuneration->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($raison);
            $entityManager->flush();

            return $this->redirectToRoute('autre_index');
        }

        if ($formRemuneration->isSubmitted() && $formRemuneration->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($remuneration);
            $entityManager->flush();

            return $this->redirectToRoute('autre_index');
        }

        return $this->render('autre/index.html.twig', [
            'raison' => $raison,
            'raisons' => $raisonRepository->findAll(),
            'form' => $form->createView(),
            'remnumeration' => $remuneration,
            'remunerations' => $remunerationRepository->findAll(),
            'formRemuneration' => $formRemuneration->createView(),
        ]);
    }

    /**
     * @Route("/modifier/{page}/{id}", name="autre_edit", methods={"GET", "POST"})
     */
    public function edit(
        Request $request,
        RaisonRepository $raisonRepository,
        CategorieRepository $categorieRepository,
        FournisseurRepository $fournisseurRepository,
        int $id = 0,
        string $page = "categorie",
        RemunerationRepository $remunerationRepository
    ): Response {
        switch ($page) {
            case 'categorie':
                $title = "Modifier Catégorie";
                $subheader = "Les Catégories";
                $subheader2 = "Modification des informations d'une catégorie";
                $route = "produit_index";

                $object = $categorieRepository->find($id);
                $form = $this->createForm(CategorieType::class, $object);
                break;
            case 'fournisseur':
                $title = "Modifier Fournisseur";
                $subheader = "Les Fournisseurs";
                $subheader2 = "Modification des informations d'un fournisseur";
                $route = "client_index";

                $object = $fournisseurRepository->find($id);
                $form = $this->createForm(FournisseurType::class, $object);
                break;
            case 'raison':
                $title = "Modifier Raison";
                $subheader = "Les Raisons";
                $subheader2 = "Modification du motif de l'annulation";
                $route = "autre_index";

                $object = $raisonRepository->find($id);
                $form = $this->createForm(RaisonType::class, $object);
                break;
            case 'remuneration':
                $title = "Modifier Rémunération";
                $subheader = "Les Rémunérations";
                $subheader2 = "Modification le montant d'un rémunération";
                $route = "autre_index";

                $object = $remunerationRepository->find($id);
                $form = $this->createForm(RemunerationType::class, $object);
                break;
            default:
                return $this->redirectToRoute("administrateur_index");
                break;
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($object);
            $entityManager->flush();
            return $this->redirectToRoute($route);
        }

        return $this->render('autre/edit.html.twig', [
            'title' => $title,
            'subheader' => $subheader,
            'subheader_2' => $subheader2,
            'route' => $route,
            'form' => $form->createView(),
        ]);
    }
}
