<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Offre;
use App\Entity\OffreProduit;
use App\Form\CreationDetailType;
use App\Form\CreationOffreType;
use App\Repository\RaisonRepository;
use App\Repository\CategorieRepository;
use App\Repository\ClientRepository;
use App\Repository\DocumentRepository;
use App\Repository\OffreProduitRepository;
use App\Repository\OffreRepository;
use App\Repository\ProduitRepository;
use App\Repository\SuperviseurRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * @Route("/offres")
 * @Security("is_granted('ROLE_USER')")
 */
class OfferController extends AbstractController
{
    /**
     * @Route("/", name="offer_index", methods={"GET"})
     */
    public function index(
        OffreProduitRepository $offreProduitRepository,
        OffreRepository $offreRepository
    ): Response {
        $produitsParoffre = [];
        $role = $this->getUser()->getRoles();
        switch ($role[0]) {
            case "ROLE_AGENT":
                $offres = $offreRepository->findBy([], ['dateCreation' => 'DESC']);
                foreach ($offres as $key => $offre) {
                    $produitsParoffre[$key][0] = $offre;
                    $produitsParoffre[$key][1] = $offreProduitRepository->findBy(['offre' => $offre->getId()]);
                }

                return $this->render('offer/index.html.twig', [
                    'offres' => $offres,
                    'produitsParoffre' => $produitsParoffre,
                ]);
                break;
            default:
                $ventes = $offreRepository->getAllVentes();
                foreach ($ventes as $key => $vente) {
                    $produitsParoffre[$key][0] = $vente;
                    $produitsParoffre[$key][1] = $offreProduitRepository->findBy(['offre' => $vente->getId()]);

                    $listOfStatusProximus = [];
                    $listOfStatusTingis = [];
                    foreach ($produitsParoffre[$key][1] as $value) {
                        $listOfStatusProximus[] = $value->getStatutProximus();
                        $listOfStatusTingis[] = $value->getStatutTingis();
                    }
                }

                return $this->render('offer/index.html.twig', [
                    'offres' => $ventes,
                    'produitsParvente' => $produitsParoffre,
                ]);
                break;
        }
    }

    /**
     * @Route("/nouvelle/{id}", name="offer_new", methods={"GET", "POST"})
     */
    public function new(
        ClientRepository $clientRepository,
        CategorieRepository $categorieRepository,
        Request $request,
        ProduitRepository $produitRepository,
        int $id = 0
    ): Response {
        $offre = new Offre();
        $offre->setClient($clientRepository->find($id));
        $offre->setAgent($this->getUser());
        $categories = $categorieRepository->findAll();
        $produitsParCategories = [];

        foreach ($categories as $key => $categorie) {
            $produitsParCategories[$key][0] = $categorie;
            $produitsParCategories[$key][1] = $produitRepository->findBy(['categorie' => $categorie->getId()]);
        }

        //  dd($produitsParCategories);

        $form = $this->createForm(CreationOffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $offre->setDateCreation(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($offre);
            $entityManager->flush();
            $listPdt = [];
            foreach ($request->request->get('produits_checkbox') as $key => $id) {
                $listPdt[$key] = $produitRepository->findById($id);
                $pdt = $listPdt[$key][0];
                foreach ($request->request->get('quantite') as $key => $qte) {
                    if ($key == $pdt->getId()) {
                        $offreProduit = new OffreProduit();
                        $offreProduit->setProduit($pdt);
                        $offreProduit->setQte($qte);
                        if ($form->getClickedButton() === $form->get('saveAndAdd')) {
                            $offre->setDateSignature(new \DateTime());
                        }
                        $offreProduit->setOffre($offre);
                        $offreProduit->setStatutTingis("En attente");
                        $offreProduit->setStatutProximus("En attente d'encodage");
                        $offreProduit->setDate(new \DateTime());
                        $entityManager->persist($offreProduit);
                        $entityManager->flush();
                    }
                }
            }
            return $this->redirectToRoute('offer_index');
        }

        return $this->render('offer/new.html.twig', [
            'offre' => $offre,
            'produitByCategory' => $produitsParCategories,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/modifier/{id}", name="offer_edit", methods={"GET", "POST"})
     */
    public function edit(
        OffreRepository $offreRepository,
        OffreProduitRepository $offreProduitRepository,
        CategorieRepository $categorieRepository,
        RaisonRepository $raisonRepository,
        ProduitRepository $produitRepository,
        SuperviseurRepository $superviseurRepository,
        DocumentRepository $documentRepository,
        Request $request,
        int $id = 0
    ): Response {
        $offre = $offreRepository->find($id);
        $offreProduits = $offreProduitRepository->findBy(['offre' => $offre->getId()]);
        $categories = $categorieRepository->findAll();
        $produitsParCategories = [];

        foreach ($categories as $key => $categorie) {
            $produitsParCategories[$key][0] = $categorie;
            $produitsParCategories[$key][1] = $offreProduitRepository->getProduitOffre($offre->getId(), $categorie->getId());
            $produitsParCategories[$key][2] = $produitRepository->findBy(['categorie' => $categorie->getId()]);
        }

        $form = $this->createForm(CreationOffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dd($offreProduits);
            $this->addFlash('success_offer', 'La mise à jour est effectuée.');

            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->flush();
            // $listPdt = [];
            // $listPdtOffre = [];

            // foreach ($offreProduits as $key => $o) {
            //     $listPdtOffre[$key] = $o;
            //     $op = $listPdtOffre[$key];
            //     foreach ($request->request->get('produits_checkbox') as $key => $id) {
            //         $listPdt[$key] = $produitRepository->findById($id);
            //         $pdt = $listPdt[$key][0];
            //         foreach ($request->request->get('quantite') as $key => $qte) {
            //             if ($key == $pdt->getId()) {
            //                 $op->setQte(intval($qte));
            //                 $entityManager->flush();
            //             } elseif ($key != $pdt->getId() && $key == $op->getProduit()->getId()) {
            //                 $entityManager->remove($op);
            //                 $entityManager->flush();
            //             } else {
            //                 $offreProduit = new OffreProduit();
            //                 $offreProduit->setProduit($pdt);
            //                 $offreProduit->setQte(intval($qte));
            //                 $offreProduit->setOffre($offre);
            //                 $offreProduit->setStatutTingis("attente");
            //                 $offreProduit->setDate(new \DateTime());
            //                 $entityManager->persist($offreProduit);
            //                 $entityManager->flush();
            //             }
            //         }
            //     }
            // }
        }

        return $this->render('offer/edit.html.twig', [
            'offre' => $offre,
            'produit' => $offreProduits,
            'raisons' => $raisonRepository->findAll(),
            'superviseurs' => $superviseurRepository->findAll(),
            'fichiers' => $documentRepository->findBy(['offre' => $offre->getId()]),
            'produitByCategory' => $produitsParCategories,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/detail/{id}", name="offer_show", methods={"GET", "POST"})
     */
    public function show(
        OffreRepository $offreRepository,
        OffreProduitRepository $offreProduitRepository,
        CategorieRepository $categorieRepository,
        RaisonRepository $raisonRepository,
        ProduitRepository $produitRepository,
        SuperviseurRepository $superviseurRepository,
        DocumentRepository $documentRepository,
        Request $request,
        int $id = 0
    ): Response {
        $offre = $offreRepository->find($id);
        $offreProduits = $offreProduitRepository->findBy(['offre' => $offre->getId()]);
        $categories = $categorieRepository->findAll();
        $produitsParCategories = [];

        foreach ($categories as $key => $categorie) {
            $produitsParCategories[$key][0] = $categorie;
            $produitsParCategories[$key][1] = $offreProduitRepository->getProduitOffre($offre->getId(), $categorie->getId());
        }

        $form = $this->createForm(CreationDetailType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dd($offreProduits);
            $this->addFlash('success_offer', 'La mise à jour est effectuée.');
        }

        return $this->render('offer/detail.html.twig', [
            'offre' => $offre,
            'produit' => $offreProduits,
            'raisons' => $raisonRepository->findAll(),
            'superviseurs' => $superviseurRepository->findAll(),
            'fichiers' => $documentRepository->findBy(['offre' => $offre->getId()]),
            'produitByCategory' => $produitsParCategories,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/valider/{id}", name="offer_valid", methods={"GET"})
     */
    public function valid(
        OffreRepository $offreRepository,
        OffreProduitRepository $offreProduitRepository,
        CategorieRepository $categorieRepository,
        RaisonRepository $raisonRepository,
        ProduitRepository $produitRepository,
        Request $request,
        int $id = 0
    ): Response {
        $offre = $offreRepository->find($id);
        $offre->setDateSignature(new \DateTime());
        $raisons = $raisonRepository->findAll();

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($offre);
        $entityManager->flush();

        $produits = $offreProduitRepository->findBy(['offre' => $offre->getId()]);
        $categories = $categorieRepository->findAll();
        $produitsParCategories = [];

        foreach ($categories as $key => $categorie) {
            $produitsParCategories[$key][0] = $categorie;
            $produitsParCategories[$key][1] = $produitRepository->findBy(['categorie' => $categorie->getId()]);
        }

        $form = $this->createForm(CreationOffreType::class, $offre);
        $form->handleRequest($request);
        $this->addFlash('success_offer', 'L\'offre a été validée');

        return $this->render('offer/edit.html.twig', [
            'offre' => $offre,
            'produit' => $produits,
            'raisons' => $raisons,
            'produitByCategory' => $produitsParCategories,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/annuler/{id}", name="offer_cancel", methods={"GET", "POST"})
     */
    public function cancel(
        OffreRepository $offreRepository,
        OffreProduitRepository $offreProduitRepository,
        CategorieRepository $categorieRepository,
        RaisonRepository $raisonRepository,
        ProduitRepository $produitRepository,
        Request $request,
        int $id = 0
    ): Response {
        $offre = $offreRepository->find($id);
        $offre->setDateAnnulation(new \DateTime());
        $raison = $raisonRepository->find($request->request->get('raison'));
        $offre->setRaison($raison);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($offre);
        $entityManager->flush();

        $produits = $offreProduitRepository->findBy(['offre' => $offre->getId()]);
        $categories = $categorieRepository->findAll();
        $produitsParCategories = [];

        foreach ($categories as $key => $categorie) {
            $produitsParCategories[$key][0] = $categorie;
            $produitsParCategories[$key][1] = $produitRepository->findBy(['categorie' => $categorie->getId()]);
        }

        $form = $this->createForm(CreationOffreType::class, $offre);
        $form->handleRequest($request);
        $this->addFlash('success_offer', 'L\'offre a été annulée');

        return $this->render('offer/edit.html.twig', [
            'offre' => $offre,
            'produit' => $produits,
            'produitByCategory' => $produitsParCategories,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/excel/{id}/{page}", name="excel", methods={"GET","POST"})
     */
    public function GenererExcel(
        OffreProduitRepository $offreProduitRepository,
        DocumentRepository $documentRepository,
        Request $request,
        Offre $offre,
        string $page = "offer_edit"
    ): Response {
        $produits = $offreProduitRepository->findBy(['offre' => $offre->getId()]);
        $listPdt = "";
        foreach ($produits as $key => $pdt) {
            $listPdt .= $pdt->getProduit()->getDesignation() . " + ";
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $evenRow = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'a6a6a6'
                ]
            ]
        ];
        $spreadsheet->getDefaultStyle()
            ->getFont()
            ->setName('Arial')
            ->setSize(12);

        $sheet->setCellValue('A1', 'Descriptif');
        $sheet->setCellValue('B1', '');
        // set font style de l'entete
        $spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
        // set cell alignment
        $spreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        //setting column width
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(70);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(70);

        // remplissage des cellules
        $sheet->setCellValue('A2', "Nom du client");
        $sheet->setCellValue('B2', $offre->getClient()->getNom());
        $sheet->setCellValue('A3', "Prénom du client");
        $sheet->setCellValue('B3', $offre->getClient()->getPrenom());
        $sheet->setCellValue('A4', "Date de naissance");
        $sheet->setCellValue('B4', $offre->getClient()->getDateNaissance());
        $sheet->setCellValue('A5', "Nom de la société");
        $sheet->setCellValue('B5', $offre->getClient()->getNomSociete());
        $sheet->setCellValue('A6', "N° de TVA");
        $sheet->setCellValue('B6', $offre->getClient()->getNumTVA());
        $sheet->setCellValue('A7', "Date de Création STE");
        $sheet->setCellValue('B7', $offre->getClient()->getDateCreationSTE());
        $sheet->setCellValue('A8', "N° de client Proximus (si existant)");
        $sheet->setCellValue('B8', $offre->getClient()->getNumClientProximus());
        $sheet->setCellValue('A9', "Numéro de téléphone fixe");
        $sheet->setCellValue('B9', $offre->getClient()->getNumTelephoneFixe());
        $sheet->setCellValue('A10', "Numéro de N° de GSM");
        $sheet->setCellValue('B10', $offre->getClient()->getNumGSM());
        $sheet->setCellValue('A11', "Adresse mail");
        $sheet->setCellValue('B11', $offre->getClient()->getEmail());
        $sheet->setCellValue('A12', "num IBAN ( si JO)");
        $sheet->setCellValue('B12', $offre->getClient()->getNumIBAN());
        $sheet->setCellValue('A13', "Code postal");
        $sheet->setCellValue('B13', $offre->getClient()->getCodePostal());
        $sheet->setCellValue('A14', "Commune");
        $sheet->setCellValue('B14', $offre->getClient()->getCommune());
        $sheet->setCellValue('A15', "Nom de rue, numéro de porte");
        $sheet->setCellValue('B15', $offre->getClient()->getRue() . ' ,' . $offre->getClient()->getNumPorte());
        $sheet->setCellValue('A16', "Si elle est différent, adresse d'installation des services");
        $sheet->setCellValue('B16', $offre->getClient()->getAdresseInstallation());
        $sheet->setCellValue('A17', "Produit(s) existant(s) Proximus");
        $sheet->setCellValue('B17', '');
        $sheet->setCellValue('A18', "Produit(s) à la concurrence");
        $sheet->setCellValue('B18', '');
        $sheet->setCellValue('A19', "Produit(s) vendu(s)");
        $sheet->setCellValue('B19', $listPdt);
        $sheet->setCellValue('A20', "Prix Plein et Prix Promos");
        $sheet->setCellValue('B20', '');
        $sheet->setCellValue('A21', "Dates d'installation souhaitées (3)");
        $sheet->setCellValue('B21', $offre->getClient()->getDateInstallation());
        $sheet->setCellValue('A22', "Information pour l’encodage");
        $sheet->setCellValue('B22', $offre->getClient()->getInfoEncodage());
        $sheet->setCellValue('A23', "N° de téléphone (fixe et/ou mobile) à porter vers Proximus");
        $sheet->setCellValue('B23', $offre->getClient()->getNumTelephoneProximus());
        $sheet->setCellValue('A24', "N° de client chez la concurrence");
        $sheet->setCellValue('B24', $offre->getClient()->getNumClientConcurrence());
        $sheet->setCellValue('A25', "N° d'easy switch");
        $sheet->setCellValue('B25', $offre->getClient()->getNumEasySwitch());
        $sheet->setCellValue('A26', "N° de carte sim du/des numéro(s) de GSM à porter (seulement si carte prépayée)");
        $sheet->setCellValue('B26', $offre->getClient()->getNumCarteSIMPrepayee());
        $sheet->setCellValue('A27', "nom client mobile concurrence et code client");
        $sheet->setCellValue('B27', $offre->getClient()->getNomClientMobile());
        $sheet->setCellValue('A28', "choix de l'app pour le mobile (Facebook, etc)");
        $sheet->setCellValue('B28', $offre->getClient()->getAppMobile());
        $sheet->setCellValue('A29', "Bonus TV / bouquet");
        $sheet->setCellValue('B29', $offre->getClient()->getBonusTV());
        $sheet->setCellValue('A30', "lien partageable de l'enregistrement (drive)");
        $sheet->setCellValue('B30', $offre->getClient()->getLienDrivePartageable());
        $sheet->setCellValue('A31', "Carte Identité");
        $sheet->setCellValue('B31', $offre->getClient()->getCarteIdentite());
        $sheet->setCellValue('A32', "Commentaire");
        $sheet->setCellValue('B32', '');

        $spreadsheet->getActiveSheet()->getStyle('A1:A32')->applyFromArray($evenRow);
        $spreadsheet->getActiveSheet()->getStyle('A1:B32')->getBorders()->applyFromArray(
            [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => [
                        'rgb' => '000000'
                    ]
                ]
            ]
        );

        // UTILISER POUR LE DOWNLOAD - header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // UTILISER POUR LE DOWNLOAD - header('Content-Disposition: attachment;filename="' . 'Fiche descriptive - ' . $offre->getCode() . '.xlsx"');
        //  $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        $writer = new Xlsx($spreadsheet);
        $writer->save('Fiche descriptive - ' . $offre->getCode() . '.xlsx');

        $documentFind = $documentRepository->findBy(['url' => 'Fiche descriptive - ' . $offre->getCode() . '.xlsx']);
        if (!$documentFind) {
            $document = new Document();
            $document->setNom('Fiche descriptive - ' . $offre->getCode() . '.xlsx');
            $document->setUrl('Fiche descriptive - ' . $offre->getCode() . '.xlsx');
            $document->setOffre($offre);
            $document->setType("Excel");

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($document);
            $entityManager->flush();
        }
        //  UTILISER POUR LE DOWNLOAD - $writer->save('php://output');
        $this->addFlash('success_offer', 'Le fichier Excel a été généré');

        return $this->redirectToRoute($page, ['id' => $offre->getId()]);
    }
}
