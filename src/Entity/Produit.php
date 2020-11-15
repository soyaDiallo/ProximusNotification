<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProduitRepository::class)
 */
class Produit
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
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $designation;

    /**
     * @ORM\OneToMany(targetEntity=OffreProduit::class, mappedBy="produit")
     */
    private $offreProduits;

    /**
     * @ORM\ManyToOne(targetEntity=Categorie::class, inversedBy="produits")
     */
    private $categorie;

    /**
     * @ORM\OneToMany(targetEntity=ProduitRemuneration::class, mappedBy="produit")
     */
    private $produitRemunerations;

    public function __construct()
    {
        $this->offreProduits = new ArrayCollection();
        $this->produitRemunerations = new ArrayCollection();
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

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

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
            $offreProduit->setProduit($this);
        }

        return $this;
    }

    public function removeOffreProduit(OffreProduit $offreProduit): self
    {
        if ($this->offreProduits->contains($offreProduit)) {
            $this->offreProduits->removeElement($offreProduit);
            // set the owning side to null (unless already changed)
            if ($offreProduit->getProduit() === $this) {
                $offreProduit->setProduit(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->designation;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
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
            $produitRemuneration->setProduit($this);
        }

        return $this;
    }

    public function removeProduitRemuneration(ProduitRemuneration $produitRemuneration): self
    {
        if ($this->produitRemunerations->contains($produitRemuneration)) {
            $this->produitRemunerations->removeElement($produitRemuneration);
            // set the owning side to null (unless already changed)
            if ($produitRemuneration->getProduit() === $this) {
                $produitRemuneration->setProduit(null);
            }
        }

        return $this;
    }
}
