<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "user" = "User",
 *     "agent" = "Agent",
 *     "backOffice" = "BackOffice",
 *     "superviseur" = "Superviseur",
 *     "administrateur" = "Administrateur",
 * })
 */

class User implements UserInterface
{
    const ROLES_LIST = [
        // 'ROLE_ADMIN', admin role is created in a secure way not login
        'Agent' => 'ROLE_AGENT',
        'BackOffice' => 'ROLE_BACKOFFICE',
        'Superviseur' => 'ROLE_SUPERVISEUR',
        'Administrateur' => 'ROLE_ADMINISTRATEUR',
    ];
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"list_notification"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"list_notification"})
     */
    protected $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"list_notification"})
     */
    protected $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"list_notification"})
     */
    protected $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"list_notification"})
     */
    protected $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $tel;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="emetteur")
     */
    private $emetteurNotifications;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="recepteur")
     */
    private $recepteurNotifications;

    public function __construct()
    {
        $this->emetteurNotifications = new ArrayCollection();
        $this->recepteurNotifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    // AJOUTER manuellement
    public function getRolesList(): array
    {
        return array_unique(self::ROLES_LIST);
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }
    public function __toString()
    {
        return $this->email;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getEmetteurNotifications(): Collection
    {
        return $this->emetteurNotifications;
    }

    public function addEmetteurNotification(Notification $emetteurNotification): self
    {
        if (!$this->emetteurNotifications->contains($emetteurNotification)) {
            $this->emetteurNotifications[] = $emetteurNotification;
            $emetteurNotification->setEmetteur($this);
        }

        return $this;
    }

    public function removeEmetteurNotification(Notification $emetteurNotification): self
    {
        if ($this->emetteurNotifications->contains($emetteurNotification)) {
            $this->emetteurNotifications->removeElement($emetteurNotification);
            // set the owning side to null (unless already changed)
            if ($emetteurNotification->getEmetteur() === $this) {
                $emetteurNotification->setEmetteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getRecepteurNotifications(): Collection
    {
        return $this->recepteurNotifications;
    }

    public function addRecepteurNotification(Notification $recepteurNotification): self
    {
        if (!$this->recepteurNotifications->contains($recepteurNotification)) {
            $this->recepteurNotifications[] = $recepteurNotification;
            $recepteurNotification->setRecepteur($this);
        }

        return $this;
    }

    public function removeRecepteurNotification(Notification $recepteurNotification): self
    {
        if ($this->recepteurNotifications->contains($recepteurNotification)) {
            $this->recepteurNotifications->removeElement($recepteurNotification);
            // set the owning side to null (unless already changed)
            if ($recepteurNotification->getRecepteur() === $this) {
                $recepteurNotification->setRecepteur(null);
            }
        }

        return $this;
    }
}
