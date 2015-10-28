<?php

namespace CuteNinja\MemoriaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class BaseEntity
 *
 * @package CuteNinja\MemoriaBundle\Entity
 */
class BaseEntity
{
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", columnDefinition="ENUM('active', 'inactive')", length=25, nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={"active", "inactive"})
     */
    protected $status = self::STATUS_ACTIVE;

    /**
     * @var string $status
     *
     * @ORM\Column(name="creation", type="datetime", nullable=false)
     *
     * @Assert\NotNull()
     * @Assert\Type(type="DateTime")
     */
    protected $creation;

    public function __construct()
    {
        $this->creation = new \DateTime();
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreation()
    {
        return $this->creation;
    }

    /**
     * @param \DateTime $creation
     *
     * @return $this
     */
    public function setCreation(\DateTime $creation)
    {
        $this->creation = $creation ? $creation : new \DateTime();

        return $this;
    }
}