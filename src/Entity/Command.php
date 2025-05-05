<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Command
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $co_id = null;

    public function getCoId(): ?int
    {
        return $this->co_id;
    }

    #[ORM\Column(type: '\DateTimeInterface')]
    private ?DateTimeInterface $co_date = null;

    public function getCoDate(): ?DateTimeInterface
    {
        return $this->co_date;
    }

    public function setCoDate(\DateTimeInterface $co_date): self
    {
        $this->co_date = $co_date;
        return $this;
    }

    #[ORM\Column(type: 'float')]
    private ?float $co_prix = null;

    public function getCoPrix(): ?float
    {
        return $this->co_prix;
    }

    public function setCoPrix(float $co_prix): self
    {
        $this->co_prix = $co_prix;
        return $this;
    }

}