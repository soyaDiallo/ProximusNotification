<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\DocumentNotification;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Repository\OffreRepository;
use App\Repository\SuperviseurRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/notification")
 * @Security("is_granted('ROLE_USER')")
 */
class NotificationController extends AbstractController
{
    /**
     * @Route("/notifier", name="notifier", methods={"GET", "POST"})
     */
    public function notification(
        OffreRepository $offreRepository,
        SuperviseurRepository $superviseurRepository,
        SluggerInterface $slugger,
        Request $request
    ): Response {
        $offre = $offreRepository->find($request->request->get('offre'));
        $recepteur = $superviseurRepository->find($request->request->get('recepteur'));
        $entityManager = $this->getDoctrine()->getManager();
        $audios = $request->files->get('myfile');
        $mesAudios = [];

        $notification = new Notification();
        $notification->setDateCreation(new \DateTime());
        $notification->setOffre($offre);
        $notification->setEmetteur($this->getUser());
        $notification->setRecepteur($recepteur);

        $entityManager->persist($notification);
        $entityManager->flush();

        foreach ($audios as $key => $audio) {
            $mesAudios[$key] = new Document();
            if ($audio) {
                $originalFilename = pathinfo($audio->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $audio->guessExtension();
                try {
                    $audio->move(
                        $this->getParameter('audio_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $mesAudios[$key]->setNom("Fichier Audio - " . $offre->getCode() . " - " . $newFilename);
                $mesAudios[$key]->setUrl($newFilename);
                $mesAudios[$key]->setOffre($offre);
                $mesAudios[$key]->setType("Audio");
            }
            $entityManager->persist($mesAudios[$key]);
            $entityManager->flush();

            $notificationDocument = new DocumentNotification();
            $notificationDocument->setNotification($notification);
            $notificationDocument->setDocument($mesAudios[$key]);
            $entityManager->persist($notificationDocument);
            $entityManager->flush();
        }
        $this->addFlash('success_offer', 'Le superviseur a été notifié de l\'offre.');

        return $this->redirectToRoute('offer_edit', ['id' => $offre->getId()]);
    }

    /**
     * @Route("/", name="notification_index", methods={"GET"})
     */
    public function getNotifications(
        NotificationRepository $notificationRepository,
        SerializerInterface $serializer
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'code' => 403,
                'message' => "Access denied"
            ], 403);
        }

        $notifications = $notificationRepository->findBy(["dateLecture" => null, 'recepteur' => $user], ["dateCreation" => "DESC"]);
        $json = $serializer->serialize(
            [
                'notifications' => $notifications,
                'count' => $notificationRepository->count(["dateLecture" => null, 'recepteur' => $user])
            ],
            'json',
            ['groups' => 'list_notification']
        );
        return JsonResponse::fromJsonString($json);

        // } else if ($p == "lus") {
        //     dd($notificationRepository->createQueryBuilder('n')->where('n.dateLecture IS NOT NULL')->where('n.recepteur = ?1')->setParameter(1, $user)->orderBy('n.dateCreation', 'DESC')->getQuery()->getResult());

        //     return $this->render('superviseur/index.html.twig', [
        //         'notifications' => $notificationRepository->createQueryBuilder('n')->where('n.dateLecture IS NOT NULL')->where('n.recepteur = ?1')->setParameter(1, $user)->orderBy('n.dateCreation', 'DESC')->getQuery()->getResult(),
        //         //  'notifications' => $notificationRepository->findBy(["dateLecture" => null, 'recepteur' => $user], ["dateCreation" => "DESC"]),
        //         'action' => 'Lus'
        //     ]);
        // } else if ($p == "envoye") {
        // }
    }

    /**
     * @Route("/lecture/{notification}/{offre}", name="notification_read", methods={"GET", "POST"})
     */
    public function read(
        NotificationRepository $notificationRepository,
        int $notification,
        int $offre
    ): Response {
        $notification = $notificationRepository->find($notification);
        $notification->setDateLecture(new \DateTime());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($notification);
        $entityManager->flush();

        return $this->redirectToRoute('offer_edit', ['id' => $offre]);
    }

    // /**
    //  * @Route("/new", name="notification_new", methods={"GET","POST"})
    //  */
    // public function new(Request $request): Response
    // {
    //     $notification = new Notification();
    //     $form = $this->createForm(NotificationType::class, $notification);
    //     $form->handleRequest($request);
    //     dd($request);
    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager = $this->getDoctrine()->getManager();
    //         $entityManager->persist($notification);
    //         $entityManager->flush();

    //         return $this->redirectToRoute('notification_index');
    //     }

    //     return $this->render('notification/new.html.twig', [
    //         'notification' => $notification,
    //         'form' => $form->createView(),
    //     ]);
    // }

    // /**
    //  * @Route("/{id}", name="notification_show", methods={"GET"})
    //  */
    // public function show(Notification $notification): Response
    // {
    //     return $this->render('notification/show.html.twig', [
    //         'notification' => $notification,
    //     ]);
    // }

    // /**
    //  * @Route("/{id}/edit", name="notification_edit", methods={"GET","POST"})
    //  */
    // public function edit(Request $request, Notification $notification): Response
    // {
    //     $form = $this->createForm(NotificationType::class, $notification);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $this->getDoctrine()->getManager()->flush();

    //         return $this->redirectToRoute('notification_index');
    //     }

    //     return $this->render('notification/edit.html.twig', [
    //         'notification' => $notification,
    //         'form' => $form->createView(),
    //     ]);
    // }

    // /**
    //  * @Route("/{id}", name="notification_delete", methods={"DELETE"})
    //  */
    // public function delete(Request $request, Notification $notification): Response
    // {
    //     if ($this->isCsrfTokenValid('delete'.$notification->getId(), $request->request->get('_token'))) {
    //         $entityManager = $this->getDoctrine()->getManager();
    //         $entityManager->remove($notification);
    //         $entityManager->flush();
    //     }

    //     return $this->redirectToRoute('notification_index');
    // }
}
