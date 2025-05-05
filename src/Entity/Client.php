<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $cu_id = null;

    public function getCuId(): ?int
    {
        return $this->cu_id;
    }

    #[ORM\Column(type: 'string', length: 30)]
    private ?string $cu_nom = null;

    public function getCuNom(): ?string
    {
        return $this->cu_nom;
    }

    public function setCuNom(string $cu_nom): self
    {
        $this->cu_nom = $cu_nom;
        return $this;
    }

    #[ORM\Column(type: 'string', length: 30)]
    private ?string $cu_prenom = null;

    public function getCuPrenom(): ?string
    {
        return $this->cu_prenom;
    }

    public function setCuPrenom(string $cu_prenom): self
    {
        $this->cu_prenom = $cu_prenom;
        return $this;
    }

}