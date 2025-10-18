<?php

namespace App\Entity;

use App\Repository\RendezvousRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezvousRepository::class)]
#[ORM\Index(columns: ['actif'], name: 'idx_rendezvous_actif')]
#[ORM\Index(columns: ['date'], name: 'idx_rendezvous_date')]
#[ORM\UniqueConstraint(columns: ["dentiste_id", "date"])]

class Rendezvous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'rendezvouses_dentiste')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $dentiste = null;

    #[ORM\ManyToOne (inversedBy: 'rendezvouses_patient')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $patient = null;

    #[ORM\Column(length: 255)]
    private ?string $service = null;

    #[ORM\Column(length: 255)]
    private ?string $observation = null;

    #[ORM\Column]
    private ?bool $actif = null;

    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDentiste(): ?Utilisateur
    {
        return $this->dentiste;
    }

    public function setDentiste(?Utilisateur $dentiste): static
    {
        $this->dentiste = $dentiste;

        return $this;
    }

    public function getPatient(): ?Utilisateur
    {
        return $this->patient;
    }

    public function setPatient(?Utilisateur $patient): static
    {
        $this->patient = $patient;

        return $this;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(string $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(string $observation): static
    {
        $this->observation = $observation;

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
}
