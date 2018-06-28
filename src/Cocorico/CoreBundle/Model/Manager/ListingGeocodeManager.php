<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 26/6/18
 * Time: 2:51 PM
 */

namespace Cocorico\CoreBundle\Model\Manager;

use Cocorico\GeoBundle\Entity\Area;
use Cocorico\GeoBundle\Entity\Coordinate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PhpParser\Node\Scalar\String_;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Cocorico\GeoBundle\Entity\City;
use Cocorico\GeoBundle\Entity\Department;

class ListingGeocodeManager extends BaseManager {
    protected $em;

    /**
     * @param EntityManager   $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    /**
     * @param Area $area
     * @param String $name
     * @return Area $saved_area
     */
    public function saveArea(Area $area, $name){
       $this->em->persist($area);
       $this->em->flush();
       $areaTranslation = $area->translate();
       $areaTranslation->setName($name);
       $areaTranslation->setLocale('en');
       $this->em->persist($areaTranslation);
       $this->em->flush();
       return $area;
    }

    /**
     * @param City $city
     * @param String $name
     * @return City $saved_city
     */
    public function saveCity(City $city, $name){
        $this->em->persist($city);
        $this->em->flush();
        $cityTranslation = $city->translate();
        $cityTranslation->setName($name);
        $cityTranslation->setLocale('en');
        $this->em->persist($cityTranslation);
        $this->em->flush();
        return $city;
    }

    /**
     * @param Department $department
     * @param String $name
     * @return Department $department
     */
    public function saveDepartment(Department $department, $name){
        $this->em->persist($department);
        $this->em->flush();
        $departmentTranslation = $department->translate();
        $departmentTranslation->setName($name);
        $departmentTranslation->setLocale('en');
        $this->em->persist($departmentTranslation);
        $this->em->flush();
        return $department;
    }

    /**
     * @param Coordinate $coordinate
     * @return Coordinate $coordinate
     */
    public function saveCoordinate(Coordinate $coordinate){
        $this->em->persist($coordinate);
        $this->em->flush();
        return $coordinate;
    }

    /**
     * @param array $listingLocationsToBeSaved
     * @param Coordinate $coordinate
     * @return bool
     */
    public function editLocation($listingLocationsToBeSaved, $coordinate){
        foreach ($listingLocationsToBeSaved as $listingLocation){
            $listingLocation->setCoordinate($coordinate);
            $this->em->persist($listingLocation);
        }
        $this->em->flush();
        return true;
    }
}