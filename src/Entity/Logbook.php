<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="logbook")
 * @ORM\HasLifecycleCallbacks()
 */
class Logbook
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     *
     */
    private $user_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $book_id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_take;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_return;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?int
    {
        return $this->user_id;
    }

    public function setUser(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getBook(): ?int
    {
        return $this->book_id;
    }

    public function setBook(int $book_id): self
    {
        $this->book_id = $book_id;

        return $this;
    }

    public function getDateTake(): string
    {
        return $this->date_take->format('Y\-m\-d h:i:s');
    }

    /**
     * @param DateTime $date_take
     * @return DateTime
     */
    public function setDateTake(DateTime $date_take): DateTime
    {
        $this->date_take = $date_take;
        return $this->date_take;
    }

    public function getDateReturn(): string
    {
        if ($this->date_return == null)
            return "not returned";
        else
            return $this->date_return->format('Y\-m\-d h:i:s');
    }

    /**
     * @param DateTime $date_return
     * @return DateTime
     */
    public function setDateReturn(DateTime $date_return): DateTime
    {
        $this->date_return = $date_return;
        return $this->date_return;
    }
}

