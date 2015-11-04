<?php

namespace CuteNinja\MemoriaBundle\Entity;

/**
 * Class BaseEntity
 *
 * @package CuteNinja\MemoriaBundle\Entity
 */
abstract class BaseEntity
{
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * @var string $status
     */
    protected $status = self::STATUS_ACTIVE;

    /**
     * @var string $status
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