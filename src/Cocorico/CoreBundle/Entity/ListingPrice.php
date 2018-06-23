<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 19/6/18
 * Time: 8:30 PM
 */

namespace Cocorico\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListingSubCategory
 *
 * @ORM\Table(name="listing_price")
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Entity\ListingSubCategoryRepository")
 */
class ListingPrice
{
    /**
     *
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Listing")
     * @ORM\JoinColumn(name="listing_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $listing_id;

    /**
     * @var int
     *
     * @ORM\Column(name="price_corporate", type="integer", nullable=true)
     */
    private $price_corporate;

    /**
     * @var int
     *
     * @ORM\Column(name="price_campus", type="integer", nullable=true)
     */
    private $price_campus;

    /**
     * @var int
     *
     * @ORM\Column(name="price_charity", type="integer", nullable=true)
     */
    private $price_charity;

    /**
     * @var int
     *
     * @ORM\Column(name="price_concert_festival", type="integer", nullable=true)
     */
    private $price_concert_festival;

    /**
     * @var int
     *
     * @ORM\Column(name="price_exhibition", type="integer", nullable=true)
     */
    private $price_exhibition;

    /**
     * @var int
     *
     * @ORM\Column(name="price_fashion_show", type="integer", nullable=true)
     */
    private $price_fashion_show;

    /**
     * @var int
     *
     * @ORM\Column(name="price_inauguration", type="integer", nullable=true)
     */
    private $price_inauguration;

    /**
     * @var int
     *
     * @ORM\Column(name="price_kids_party", type="integer", nullable=true)
     */
    private $price_kids_party;

    /**
     * @var int
     *
     * @ORM\Column(name="price_photo_videoshoot", type="integer", nullable=true)
     */
    private $price_photo_videoshoot;

    /**
     * @var int
     *
     * @ORM\Column(name="price_private_party", type="integer", nullable=true)
     */
    private $price_private_party;

    /**
     * @var int
     *
     * @ORM\Column(name="price_professional_hiring", type="integer", nullable=true)
     */
    private $price_professional_hiring;

    /**
     * @var int
     *
     * @ORM\Column(name="price_religious", type="integer", nullable=true)
     */
    private $price_religious;

    /**
     * @var int
     *
     * @ORM\Column(name="price_restaurent", type="integer", nullable=true)
     */
    private $price_restaurent;

    /**
     * @var int
     *
     * @ORM\Column(name="price_wedding", type="integer", nullable=true)
     */
    private $price_wedding;

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
    public function setListingId($listing_id)
    {
        $this->listing_id = $listing_id;
    }

    /**
     * @return int
     */
    public function getPriceCorporate()
    {
        return $this->price_corporate;
    }

    /**
     * @param int $price_corporate
     */
    public function setPriceCorporate(int $price_corporate)
    {
        $this->price_corporate = $price_corporate;
    }

    /**
     * @return int
     */
    public function getPriceCampus()
    {
        return $this->price_campus;
    }

    /**
     * @param int $price_campus
     */
    public function setPriceCampus(int $price_campus)
    {
        $this->price_campus = $price_campus;
    }

    /**
     * @return int
     */
    public function getPriceCharity()
    {
        return $this->price_charity;
    }

    /**
     * @param int $price_charity
     */
    public function setPriceCharity(int $price_charity)
    {
        $this->price_charity = $price_charity;
    }

    /**
     * @return int
     */
    public function getPriceConcertFestival()
    {
        return $this->price_concert_festival;
    }

    /**
     * @param int $price_concert_festival
     */
    public function setPriceConcertFestival(int $price_concert_festival)
    {
        $this->price_concert_festival = $price_concert_festival;
    }

    /**
     * @return int
     */
    public function getPriceExhibition()
    {
        return $this->price_exhibition;
    }

    /**
     * @param int $price_exhibition
     */
    public function setPriceExhibition(int $price_exhibition)
    {
        $this->price_exhibition = $price_exhibition;
    }

    /**
     * @return int
     */
    public function getPriceFashionShow()
    {
        return $this->price_fashion_show;
    }

    /**
     * @param int $price_fashion_show
     */
    public function setPriceFashionShow(int $price_fashion_show)
    {
        $this->price_fashion_show = $price_fashion_show;
    }

    /**
     * @return int
     */
    public function getPriceInauguration()
    {
        return $this->price_inauguration;
    }

    /**
     * @param int $price_inauguration
     */
    public function setPriceInauguration(int $price_inauguration)
    {
        $this->price_inauguration = $price_inauguration;
    }

    /**
     * @return int
     */
    public function getPriceKidsParty()
    {
        return $this->price_kids_party;
    }

    /**
     * @param int $price_kids_party
     */
    public function setPriceKidsParty(int $price_kids_party)
    {
        $this->price_kids_party = $price_kids_party;
    }

    /**
     * @return int
     */
    public function getPricePhotoVideoshoot()
    {
        return $this->price_photo_videoshoot;
    }

    /**
     * @param int $price_photo_videoshoot
     */
    public function setPricePhotoVideoshoot(int $price_photo_videoshoot)
    {
        $this->price_photo_videoshoot = $price_photo_videoshoot;
    }

    /**
     * @return int
     */
    public function getPricePrivateParty()
    {
        return $this->price_private_party;
    }

    /**
     * @param int $price_private_party
     */
    public function setPricePrivateParty(int $price_private_party)
    {
        $this->price_private_party = $price_private_party;
    }

    /**
     * @return int
     */
    public function getPriceProfessionalHiring()
    {
        return $this->price_professional_hiring;
    }

    /**
     * @param int $price_professional_hiring
     */
    public function setPriceProfessionalHiring(int $price_professional_hiring)
    {
        $this->price_professional_hiring = $price_professional_hiring;
    }

    /**
     * @return int
     */
    public function getPriceReligious()
    {
        return $this->price_religious;
    }

    /**
     * @param int $price_religious
     */
    public function setPriceReligious(int $price_religious)
    {
        $this->price_religious = $price_religious;
    }

    /**
     * @return int
     */
    public function getPriceRestaurent()
    {
        return $this->price_restaurent;
    }

    /**
     * @param int $price_restaurent
     */
    public function setPriceRestaurent(int $price_restaurent)
    {
        $this->price_restaurent = $price_restaurent;
    }

    /**
     * @return int
     */
    public function getPriceWedding()
    {
        return $this->price_wedding;
    }

    /**
     * @param int $price_wedding
     */
    public function setPriceWedding(int $price_wedding)
    {
        $this->price_wedding = $price_wedding;
    }
}