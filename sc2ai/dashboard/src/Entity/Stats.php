<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stats
 *
 * @ORM\Table(name="stats")
 * @ORM\Entity
 */
class Stats
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var int
     *
     * @ORM\Column(name="outcome", type="integer", nullable=false)
     */
    private $outcome;

    /**
     * @var int
     *
     * @ORM\Column(name="game_score", type="integer", nullable=false)
     */
    private $gameScore;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getOutcome(): ?int
    {
        return $this->outcome;
    }

    public function setOutcome(int $outcome): self
    {
        $this->outcome = $outcome;

        return $this;
    }

    public function getGameScore(): ?int
    {
        return $this->gameScore;
    }

    public function setGameScore(int $gameScore): self
    {
        $this->gameScore = $gameScore;

        return $this;
    }


}
