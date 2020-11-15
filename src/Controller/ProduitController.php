<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Categorie;
use App\Entity\Produit;
use App\Entity\ProduitRemuneration;
use App\Form\CategorieType;
use App\Form\ProduitType;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRemunerationRepository;
use App\Repository\ProduitRepository;
use App\Repository\RemunerationRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/produits")
 * @Security("is_granted('ROLE_ADMINISTRATEUR')")
 */
class ProduitController extends AbstractController
{
    /**
     * @Route("/", name="produit_index", methods={"GET", "POST"})
     */
    public function index(ProduitRepository $produitRepository, ProduitRemunerationRepository $produitRemunerationRepository, CategorieRepository $categorieRepository, Request $request): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        $produits = $produitRepository->findAll();
        $produitsRemunerations = [];
        foreach ($produits as $key => $value) {
            $produitsRemunerations[$key][0] = $value;
            $produitsRemunerations[$key][1] = $produitRemunerationRepository->findBy(["produit" => $value], ["date" => "DESC"], 1);
        }

        //  dd($produitsRemunerations);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->redirectToRoute('produit_index');
        }

        return $this->render('produit/index.html.twig', [
            'produits' => $produitsRemunerations,
            'categories' => $categorieRepository->findAll(),
            'formCategorie' => $form->createView(),
        ]);
    }

    /**
     * @Route("/modifier/{id}", name="produit_edit", methods={"GET", "POST"})
     */
    public function edit(
        ProduitRepository $produitRepository,
        int $id = 0,
        Request $request,
        RemunerationRepository $remunerationRepository,
        ProduitRemunerationRepository $produitRemunerationRepository
    ): Response {
        $produit = $produitRepository->find($id);
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $remuneration = $remunerationRepository->find($request->get("remuneration"));
            } catch (\Exception $th) {
                return $this->redirectToRoute('produit_index');
            }
            $produitRemuneration = new ProduitRemuneration();
            $produitRemuneration->setProduit($produit);
            $produitRemuneration->setRemuneration($remuneration);
            $produitRemuneration->setDate(new \DateTime());
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($produit);
            $entityManager->persist($produitRemuneration);
            $entityManager->flush();

            return $this->redirectToRoute('produit_index');
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'remuneration' => $produitRemunerationRepository->findBy(["produit" => $produit], ["date" => "DESC"], 1)[0],
            'form' => $form->createView(),
            'remunerations' => $remunerationRepository->findAll(),
        ]);
    }

    /**
     * @Route("/nouveau", name="produit_new")
     */
    public function new(
        Request $request,
        RemunerationRepository $remunerationRepository
    ): Response {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $remuneration = $remunerationRepository->find($request->get("remuneration"));
            } catch (\Exception $th) {
                return $this->redirectToRoute('produit_index');
            }
            $produitRemuneration = new ProduitRemuneration();
            $produitRemuneration->setProduit($produit);
            $produitRemuneration->setRemuneration($remuneration);
            $produitRemuneration->setDate(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($produit);
            $entityManager->persist($produitRemuneration);
            $entityManager->flush();

            return $this->redirectToRoute('produit_index');
        }

        return $this->render('produit/new.html.twig', [
            'form' => $form->createView(),
            'remunerations' => $remunerationRepository->findAll(),
        ]);
    }

    /**
     * @Route("/import", name="import_produit", methods={"GET", "POST"})
     */
    public function import(Request $request): Response
    {
        $file = $request->files->get('myfile');
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($file);
        $data  = $spreadsheet->getActiveSheet()->toArray();
        $list = [];
        $entityManager = $this->getDoctrine()->getManager();
        foreach ($data as $key => $d) {
            $list[$key] = new Produit();
            $list[$key]->setCode($d[0]);
            $list[$key]->setDesignation($d[1]);
            $entityManager->persist($list[$key]);
            $entityManager->flush();
        }
        return $this->redirectToRoute('produit_index');
    }
}
