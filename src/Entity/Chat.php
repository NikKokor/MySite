<?php

namespace App\Entity;

use App\Repository\ChatRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ChatRepository::class)
 */
class Chat
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
    private $user_1;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $user_2;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser1(): ?string
    {
        return $this->user_1;
    }

    public function setUser1(string $user_1): self
    {
        $this->user_1 = $user_1;

        return $this;
    }

    public function getUser2(): ?string
    {
        return $this->user_2;
    }

    public function setUser2(string $user_2): self
    {
        $this->user_2 = $user_2;

        return $this;
    }
}
