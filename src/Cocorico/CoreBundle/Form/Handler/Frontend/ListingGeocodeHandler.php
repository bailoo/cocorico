<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 26/6/18
 * Time: 3:00 PM
 */

namespace Cocorico\CoreBundle\Form\Handler\Frontend;

use Cocorico\CoreBundle\Model\Manager\ListingGeocodeManager;
use Cocorico\GeoBundle\Entity\AreaTranslation;
use Cocorico\GeoBundle\Entity\City;
use Cocorico\GeoBundle\Entity\Coordinate;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Form\Handler\RegistrationFormHandler;
use Cocorico\GeoBundle\Entity\Department;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;
use Cocorico\GeoBundle\Entity\Area;

/**
 * Handle Listing Geocoding
 *
 */
class ListingGeocodeHandler
{
    protected $request;
    protected $listingGeocodeManager;

    /**
     * @param RequestStack $requestStack
     * @param ListingGeocodeManager $listingGeocodeManager
     */
    public function __construct(RequestStack $requestStack, ListingGeocodeManager $listingGeocodeManager) {
        $this->request = $requestStack->getCurrentRequest();
        $this->listingGeocodeManager = $listingGeocodeManager;
    }

    /**
     * @param Area $area
     * @param string $name
     * @return Area $area
     */
    public function processArea(Area $area, $name){
       return $this->listingGeocodeManager->saveArea($area, $name);
    }

    /**
     * @param City $city
     * @param string $name
     * @return City $city
     */
    public function processCity(City $city, $name){
        return $this->listingGeocodeManager->saveCity($city, $name);
    }

    /**
     * @param Department $department
     * @param string $name
     * @return Department $department
     */
    public function processDepartment(Department $department, $name){
        return $this->listingGeocodeManager->saveDepartment($department, $name);
    }

    /**
     * @param Coordinate $coordinate
     * @return Coordinate $coordinate
     */
    public function processCoordinate(Coordinate $coordinate){
        return $this->listingGeocodeManager->saveCoordinate(    $coordinate);
    }


    /**
     * @param array $listingLocationsToBeSaved
     * @param Coordinate $coordinate
     * @return bool $status
     */
    public function processLocation($listingLocationsToBeSaved, $coordinate){
        return $this->listingGeocodeManager->editLocation($listingLocationsToBeSaved, $coordinate);
    }
}