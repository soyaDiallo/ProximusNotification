<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=OffreRepository::class)
 */
class Offre 
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"list_notification"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"list_notification"})
     */
    private $code;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"list_notification"})
     */
    private $dateSignature;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"list_notification"})
     */
    private $dateAnnulation;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numOpportunite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list_notification"})
     */
    private $commentaire;

    /**
     * @ORM\ManyToOne(targetEntity=Agent::class, inversedBy="offres")
     */
    private $agent;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="offre")
     */
    private $documents;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="offres")
     * @Groups({"list_notification"})
     */
    private $client;

    /**
     * @ORM\OneToMany(targetEntity=OffreProduit::class, mappedBy="offre")
     */
    private $offreProduits;

    /**
     * @ORM\ManyToOne(targetEntity=Raison::class, inversedBy="offres")
     * @Groups({"list_notification"})
     */
    private $raison;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="offre")
     */
    private $notifications;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->offreProduits = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
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

    public function getDateSignature(): ?\DateTimeInterface
    {
        return $this->dateSignature;
    }

    public function setDateSignature(\DateTimeInterface $dateSignature): self
    {
        $this->dateSignature = $dateSignature;

        return $this;
    }

    public function getDateAnnulation(): ?\DateTimeInterface
    {
        return $this->dateAnnulation;
    }

    public function setDateAnnulation(\DateTimeInterface $dateAnnulation): self
    {
        $this->dateAnnulation = $dateAnnulation;

        return $this;
    }

    public function getNumOpportunite(): ?int
    {
        return $this->numOpportunite;
    }

    public function setNumOpportunite(int $numOpportunite): self
    {
        $this->numOpportunite = $numOpportunite;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getAgent(): ?Agent
    {
        return $this->agent;
    }

    public function setAgent(?Agent $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setOffre($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
            // set the owning side to null (unless already changed)
            if ($document->getOffre() === $this) {
                $document->setOffre(null);
            }
        }

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection|OffreProduit[]
     */
    public function getOffreProduits(): Collection
    {
        return $this->offreProduits;
    }

    public function addOffreProduit(OffreProduit $offreProduit): self
    {
        if (!$this->offreProduits->contains($offreProduit)) {
            $this->offreProduits[] = $offreProduit;
            $offreProduit->setOffre($this);
        }

        return $this;
    }

    public function removeOffreProduit(OffreProduit $offreProduit): self
    {
        if ($this->offreProduits->contains($offreProduit)) {
            $this->offreProduits->removeElement($offreProduit);
            // set the owning side to null (unless already changed)
            if ($offreProduit->getOffre() === $this) {
                $offreProduit->setOffre(null);
            }
        }

        return $this;
    }

    public function getRaison(): ?Raison
    {
        return $this->raison;
    }

    public function setRaison(?Raison $raison): self
    {
        $this->raison = $raison;

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setOffre($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
            // set the owning side to null (unless already changed)
            if ($notification->getOffre() === $this) {
                $notification->setOffre(null);
            }
        }

        return $this;
    }
}
