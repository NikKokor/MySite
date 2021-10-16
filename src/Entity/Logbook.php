<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="logbook")
 * @ORM\HasLifecycleCallbacks()
 */
class Logbook {
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
     * @ORM\Column(type="datetime")
     */
    private $date_take;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_return;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getUser() : ?int
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

    public function getDateTake(): ?\DateTime
    {
        return $this->date_take->format('Y\-m\-d h:i:s');
    }

    /**
     * @param \DateTime $date_take
     * @return Logbook
     */
    public function setDateTake(\DateTime $date_take): self
    {
        $this->date_take = $date_take;
        return $this;
    }

    public function getDateReturn(): ?\DateTime
    {
        return $this->date_return->format('Y\-m\-d h:i:s');
    }

    /**
     * @param ?\DateTime $date_return
     * @return Logbook
     */
    public function setDateReturn(?\DateTime $date_return): self
    {
        $this->date_return = $date_return;
        return $this;
    }
}

