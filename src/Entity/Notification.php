<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class Notification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"list_notification"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"list_notification"})
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"list_notification"})
     */
    private $dateLecture;

    /**
     * @ORM\ManyToOne(targetEntity=Offre::class, inversedBy="notifications")
     * @Groups({"list_notification"})
     */
    private $offre;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="emetteurNotifications")
     * @Groups({"list_notification"})
     */
    private $emetteur;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="recepteurNotifications")
     * @Groups({"list_notification"})
     */
    private $recepteur;

    /**
     * @ORM\OneToMany(targetEntity=DocumentNotification::class, mappedBy="notification")
     */
    private $documentNotifications;

    public function __construct()
    {
        $this->documentNotifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateLecture(): ?\DateTimeInterface
    {
        return $this->dateLecture;
    }

    public function setDateLecture(\DateTimeInterface $dateLecture): self
    {
        $this->dateLecture = $dateLecture;

        return $this;
    }

    public function getOffre(): ?Offre
    {
        return $this->offre;
    }

    public function setOffre(?Offre $offre): self
    {
        $this->offre = $offre;

        return $this;
    }

    public function getEmetteur(): ?User
    {
        return $this->emetteur;
    }

    public function setEmetteur(?User $emetteur): self
    {
        $this->emetteur = $emetteur;

        return $this;
    }

    public function getRecepteur(): ?User
    {
        return $this->recepteur;
    }

    public function setRecepteur(?User $recepteur): self
    {
        $this->recepteur = $recepteur;

        return $this;
    }

    /**
     * @return Collection|DocumentNotification[]
     */
    public function getDocumentNotifications(): Collection
    {
        return $this->documentNotifications;
    }

    public function addDocumentNotification(DocumentNotification $documentNotification): self
    {
        if (!$this->documentNotifications->contains($documentNotification)) {
            $this->documentNotifications[] = $documentNotification;
            $documentNotification->setNotification($this);
        }

        return $this;
    }

    public function removeDocumentNotification(DocumentNotification $documentNotification): self
    {
        if ($this->documentNotifications->contains($documentNotification)) {
            $this->documentNotifications->removeElement($documentNotification);
            // set the owning side to null (unless already changed)
            if ($documentNotification->getNotification() === $this) {
                $documentNotification->setNotification(null);
            }
        }

        return $this;
    }
}
