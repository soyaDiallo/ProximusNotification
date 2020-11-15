<?php

namespace App\Entity;

use App\Repository\RemunerationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RemunerationRepository::class)
 */
class Remuneration
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $montant;

    /**
     * @ORM\OneToMany(targetEntity=ProduitRemuneration::class, mappedBy="remuneration")
     */
    private $produitRemunerations;

    public function __construct()
    {
        $this->produitRemunerations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function __toString()
    {
        return strval($this->getMontant());
    }

    /**
     * @return Collection|ProduitRemuneration[]
     */
    public function getProduitRemunerations(): Collection
    {
        return $this->produitRemunerations;
    }

    public function addProduitRemuneration(ProduitRemuneration $produitRemuneration): self
    {
        if (!$this->produitRemunerations->contains($produitRemuneration)) {
            $this->produitRemunerations[] = $produitRemuneration;
            $produitRemuneration->setRemuneration($this);
        }

        return $this;
    }

    public function removeProduitRemuneration(ProduitRemuneration $produitRemuneration): self
    {
        if ($this->produitRemunerations->contains($produitRemuneration)) {
            $this->produitRemunerations->removeElement($produitRemuneration);
            // set the owning side to null (unless already changed)
            if ($produitRemuneration->getRemuneration() === $this) {
                $produitRemuneration->setRemuneration(null);
            }
        }

        return $this;
    }

}
