<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BotApiChatRepository")
 */
class BotApiChat
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $updateId;

    /**
     * @ORM\Column(type="integer")
     */
    private $fromUserId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $sentStamp;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sentText;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAnswered;

    /**
     * @ORM\Column(type="text")
     */
    private $answerSent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpdateId(): ?int
    {
        return $this->updateId;
    }

    public function setUpdateId(int $updateId): self
    {
        $this->updateId = $updateId;

        return $this;
    }

    public function getFromUserId(): ?int
    {
        return $this->fromUserId;
    }

    public function setFromUserId(int $fromUserId): self
    {
        $this->fromUserId = $fromUserId;

        return $this;
    }

    public function getSentStamp(): ?\DateTimeInterface
    {
        return $this->sentStamp;
    }

    public function setSentStamp(\DateTimeInterface $sentStamp): self
    {
        $this->sentStamp = $sentStamp;

        return $this;
    }

    public function getSentText(): ?string
    {
        return $this->sentText;
    }

    public function setSentText(string $sentText): self
    {
        $this->sentText = $sentText;

        return $this;
    }

    public function getIsAnswered(): ?bool
    {
        return $this->isAnswered;
    }

    public function setIsAnswered(bool $isAnswered): self
    {
        $this->isAnswered = $isAnswered;

        return $this;
    }

    public function getAnswerSent(): ?string
    {
        return $this->answerSent;
    }

    public function setAnswerSent(string $answerSent): self
    {
        $this->answerSent = $answerSent;

        return $this;
    }
}
