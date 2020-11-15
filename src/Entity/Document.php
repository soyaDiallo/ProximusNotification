<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Offre::class, inversedBy="documents")
     */
    private $offre;

    /**
     * @ORM\OneToMany(targetEntity=DocumentNotification::class, mappedBy="document")
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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
            $documentNotification->setDocument($this);
        }

        return $this;
    }

    public function removeDocumentNotification(DocumentNotification $documentNotification): self
    {
        if ($this->documentNotifications->contains($documentNotification)) {
            $this->documentNotifications->removeElement($documentNotification);
            // set the owning side to null (unless already changed)
            if ($documentNotification->getDocument() === $this) {
                $documentNotification->setDocument(null);
            }
        }

        return $this;
    }
}
