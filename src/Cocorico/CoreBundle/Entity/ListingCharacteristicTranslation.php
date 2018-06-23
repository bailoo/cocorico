<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Entity;

use Cocorico\CoreBundle\Model\BaseListingCharacteristicTranslation;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * ListingCharacteristicTranslation
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="listing_characteristic_translation")
 */
class ListingCharacteristicTranslation extends BaseListingCharacteristicTranslation
{
    use ORMBehaviors\Translatable\Translation;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\ListingCharacteristic")
     * @ORM\JoinColumn(name="translatable_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $translatableId;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTranslatableId()
    {
        return $this->translatableId;
    }

    /**
     * @param mixed $translatableId
     */
    public function setTranslatableId($translatableId): void
    {
        $this->translatableId = $translatableId;
    }

}
