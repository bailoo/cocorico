<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Model;

use Cocorico\CoreBundle\Entity\ListingImage;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ListingImage
 *
 * @ORM\MappedSuperclass
 */
abstract class BaseListingImage
{

    const IMAGE_DEFAULT = "default-listing.png";
    const IMAGE_FOLDER = "/uploads/listings/images/";

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\Column(type="string", length=255, name="name", nullable=false)
     *
     * @var string $name
     */
    protected $name;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="smallint", nullable=true)
     */
    protected $position;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\Column(type="string", length=255, name="type", nullable=false)
     *
     * @var string $name
     */
    protected $type = 'image';

    /**
     * Sets name.
     *
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set position
     *
     * @param  boolean $position
     * @return ListingImage
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return boolean
     */
    public function getPosition()
    {
        return $this->position;
    }

    public function getWebPath()
    {
        return null === $this->name ? null : self::IMAGE_FOLDER . $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
