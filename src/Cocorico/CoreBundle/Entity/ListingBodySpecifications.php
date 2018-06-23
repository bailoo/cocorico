<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 21/6/18
 * Time: 4:50 PM
 */

namespace Cocorico\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ListingBodySpecifications
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="listing_body_specification")
 */
class ListingBodySpecifications
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\Listing")
     * @ORM\JoinColumn(name="listing_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $listing_id;

    /**
     *
     * @ORM\Column(name="height", type="string", nullable=true)
     *
     * @var mixed
     */
    protected $height;

    /**
     *
     * @ORM\Column(name="bust", type="string", nullable=true)
     *
     * @var mixed
     */
    protected $bust;

    /**
     *
     * @ORM\Column(name="weight", type="string", nullable=true)
     *
     * @var mixed
     */
    protected $weight;

    /**
     *
     * @ORM\Column(name="hips", type="string", nullable=true)
     *
     * @var mixed
     */
    protected $hips;

    /**
     *
     * @ORM\Column(name="eye_color", type="string", nullable=true)
     *
     * @var mixed
     */
    protected $eyeColor;

    /**
     *
     * @ORM\Column(name="skinColor", type="string", nullable=true)
     *
     * @var mixed
     */
    protected $skinColor;

    /**
     *
     * @ORM\Column(name="tatoo_type", type="string", nullable=true)
     *
     * @var mixed
     */
    protected $tatooType;

    /**
     *
     * @ORM\Column(name="piercing_type", type="string", nullable=true)
     *
     * @var mixed
     */
    protected $piercingType;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getListingId()
    {
        return $this->listing_id;
    }

    /**
     * @param mixed $listing_id
     */
    public function setListingId($listing_id): void
    {
        $this->listing_id = $listing_id;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height): void
    {
        $this->height = $height;
    }

    /**
     * @return mixed
     */
    public function getBust()
    {
        return $this->bust;
    }

    /**
     * @param mixed $bust
     */
    public function setBust($bust): void
    {
        $this->bust = $bust;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param mixed $weight
     */
    public function setWeight($weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @return mixed
     */
    public function getHips()
    {
        return $this->hips;
    }

    /**
     * @param mixed $hips
     */
    public function setHips($hips): void
    {
        $this->hips = $hips;
    }

    /**
     * @return mixed
     */
    public function getEyeColor()
    {
        return $this->eyeColor;
    }

    /**
     * @param mixed $eyeColor
     */
    public function setEyeColor($eyeColor): void
    {
        $this->eyeColor = $eyeColor;
    }

    /**
     * @return mixed
     */
    public function getSkinColor()
    {
        return $this->skinColor;
    }

    /**
     * @param mixed $skinColor
     */
    public function setSkinColor($skinColor): void
    {
        $this->skinColor = $skinColor;
    }

    /**
     * @return mixed
     */
    public function getTatooType()
    {
        return $this->tatooType;
    }

    /**
     * @param mixed $tatooType
     */
    public function setTatooType($tatooType): void
    {
        $this->tatooType = $tatooType;
    }

    /**
     * @return mixed
     */
    public function getPiercingType()
    {
        return $this->piercingType;
    }

    /**
     * @param mixed $piercingType
     */
    public function setPiercingType($piercingType): void
    {
        $this->piercingType = $piercingType;
    }
}
