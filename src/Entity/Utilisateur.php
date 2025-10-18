<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Index(columns: ['actif'], name: 'idx_utilisateur_actif')]
#[ORM\Index(columns: ['ville'], name: 'idx_utilisateur_ville')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_CIN', fields: ['cin'])]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cette adresse e-mail.')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $genre = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $ddn = null;

    #[ORM\Column(length: 255)]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dde = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Temoinage>
     */
    #[ORM\OneToMany(targetEntity: Temoinage::class, mappedBy: 'patient')]
    private Collection $temoinages;

    /**
     * @var Collection<int, Rendezvous>
     */
    #[ORM\OneToMany(targetEntity: Rendezvous::class, mappedBy: 'dentiste')]
    private Collection $rendezvouses_dentiste;

    /**
     * @var Collection<int, Rendezvous>
     */
    #[ORM\OneToMany(targetEntity: Rendezvous::class, mappedBy: 'patient')]
    private Collection $rendezvouses_patient;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'patient')]
    private Collection $questions;

    /**
     * @var Collection<int, Reponse>
     */
    #[ORM\OneToMany(targetEntity: Reponse::class, mappedBy: 'dentiste')]
    private Collection $reponses;

    #[ORM\Column(length: 255)]
    private ?string $cin = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\Column(length: 255)]
    private ?string $ville = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $Description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $prescription = null;





    public function __construct()
    {
        $this->temoinages = new ArrayCollection();
        $this->rendezvouses = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->reponses = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isGenre(): ?bool
    {
        return $this->genre;
    }

    public function setGenre(bool $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return ucwords(strtolower($this->adresse));
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getDdn(): ?\DateTimeInterface
    {
        return $this->ddn;
    }

    public function getAge()
    {
        $dateDN = $this->ddn;
        $date = new \DateTime();
        return $date->diff($dateDN)->y;
    }

    public function setDdn(\DateTimeInterface $ddn): static
    {
        $this->ddn = $ddn;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getDde(): ?\DateTimeInterface
    {
        return $this->dde;
    }

    public function setDde(\DateTimeInterface $dde): static
    {
        $this->dde = $dde;

        return $this;
    }

    public function getNom(): ?string
    {
        return ucwords(strtolower(trim($this->nom)));
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Temoinage>
     */
    public function getTemoinages(): Collection
    {
        return $this->temoinages;
    }

    public function addTemoinage(Temoinage $temoinage): static
    {
        if (!$this->temoinages->contains($temoinage)) {
            $this->temoinages->add($temoinage);
            $temoinage->setPatient($this);
        }

        return $this;
    }

    public function removeTemoinage(Temoinage $temoinage): static
    {
        if ($this->temoinages->removeElement($temoinage)) {
            // set the owning side to null (unless already changed)
            if ($temoinage->getPatient() === $this) {
                $temoinage->setPatient(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;

    }

    /**
     * @return Collection<int, Rendezvous>
     */
    public function getRendezvouses_dentiste(): Collection
    {
        return $this->rendezvouses_dentiste;
    }

    /**
     * @return Collection<int, Rendezvous>
     */
    public function getRendezvouses_patient(): Collection
    {
        return $this->rendezvouses_patient;
    }

    public function addRendezvouse(Rendezvous $rendezvouse): static
    {
        if (!$this->rendezvouses->contains($rendezvouse)) {
            $this->rendezvouses->add($rendezvouse);
            $rendezvouse->setDentiste($this);
        }

        return $this;
    }

    public function removeRendezvouse(Rendezvous $rendezvouse): static
    {
        if ($this->rendezvouses->removeElement($rendezvouse)) {
            // set the owning side to null (unless already changed)
            if ($rendezvouse->getDentiste() === $this) {
                $rendezvouse->setDentiste(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setPatient($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getPatient() === $this) {
                $question->setPatient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reponse>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setDentiste($this);
        }

        return $this;
    }

    public function removeReponse(Reponse $reponse): static
    {
        if ($this->reponses->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getDentiste() === $this) {
                $reponse->setDentiste(null);
            }
        }

        return $this;
    }

    public function getCin(): ?string
    {
        return $this->cin;
    }

    public function setCin(string $cin): static
    {
        $this->cin = $cin;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getVille(): ?string
    {
        $villes = array(
            'al_hoceima' => 'Al Hoceïma',
            'casablanca' => 'Casablanca',
            'fes' => 'Fès',
            'kenitra' => 'Kénitra',
            'marrakech' => 'Marrakech',
            'meknes' => 'Meknès',
            'rabat' => 'Rabat',
            'sale' => 'Salé',
            'tanger' => 'Tanger',
            'tetouan' => 'Tétouan'
        );

        if (isset($villes[$this->ville])) {
            return $villes[$this->ville];
        }

        // Opcionalmente, puedes devolver un valor predeterminado o lanzar una excepción
        return null; //
    }

    public function getVilleRef(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    public function getFoto(): ?string
    {
        $buscar = glob('assets/img/userimages/photouser' . $this->getId() . '.jpg');
        $fuente = count($buscar) == 1 ? 'photouser' . $this->getId() . '.jpg' : 'user.jpg';
        return 'assets/img/userimages/' . $fuente;
    }

    public function getPrescription(): ?string
    {
        return $this->prescription;
    }

    public function setPrescription(?string $prescription): static
    {
        $this->prescription = $prescription;

        return $this;
    }

}
