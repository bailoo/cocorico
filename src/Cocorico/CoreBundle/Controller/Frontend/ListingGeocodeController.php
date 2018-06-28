<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 25/6/18
 * Time: 6:05 PM
 */

namespace Cocorico\CoreBundle\Controller\Frontend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Cocorico\GeoBundle\Entity\Department;
use Cocorico\GeoBundle\Entity\Area;
use Cocorico\GeoBundle\Entity\City;
use Cocorico\GeoBundle\Entity\Coordinate;

/**
 * Listing controller.
 *
 * @Route("/listing")
 */

class ListingGeocodeController extends Controller{
    /**
     * @author Sarthak Patidar
     *
     * Creates a Listing import entity.
     *
     * @Route("/geocode", name="cocorico_geo_import")
     * @Method({"GET"})
     *
     * @param  Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ListingGeoCodeAction(Request $request){
        $count = 0;

        if (($fp = fopen("listing_location.csv", "r")) !== FALSE) {
            $listingGeocodeHandler = $this->get('cocorico.geocode.handler.listing');
            while (($row = fgetcsv($fp)) !== FALSE) {
                $city_name = $row[0];
                if(strrpos($city_name, ' ')){
                    $city_name = str_replace(' ', '+', $city_name);
                }

                var_dump($city_name);
                $location = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".$city_name."&key=AIzaSyA7M_p1ZRJBBv-i1qmmCWkstyQvuZAg7iQ");

                $decodeLocation = JSON_decode($location)->results[0];
//                var_dump($decodeLocation);
//                print_r('<br>');
//                print_r('<br>');
                $parsedLocation = $decodeLocation->address_components;
//                var_dump($parsedLocation);
//                print_r('<br>');
//                print_r('<br>');
                $department_name_check = false;
                if(strrpos($city_name, '+')){
                    $city_name = str_replace('+', ' ', $city_name);
                }

                foreach ($parsedLocation as $component){
//                    var_dump($component);
//                    print_r('<br>');
                    $types = $component->types;
                    foreach ($types as $type){
//                        var_dump('Type: '.$type);
//                        print_r('<br>');
                        if ($type == 'administrative_area_level_1'){
                            $area_name = $component->long_name;
//                            var_dump('Area: '.$area_name);
//                            print_r('<br>');
                        } elseif ($type == 'administrative_area_level_2'){
                            $department_name_check = true;
                            $department_name = $component->long_name;
//                            var_dump('Dept.: '.$department_name);
//                            print_r('<br>');
                        }
                    }
//                    print_r('<br><br>');
                }

                if(!$department_name_check){
                    $department_name = $city_name;
                }

                $saved_coordinate = $this->findCity($city_name, $department_name, $area_name, $decodeLocation);
//                $listingLocations = $this->getDoctrine()->getRepository('CocoricoCoreBundle:ListingLocation')->findAll();
//                $listingLocationsToBeSaved = array();
//                foreach ($listingLocations as $listingLocation){
//                    if ($listingLocation->getCity() == $city_name and $listingLocation->getCoordiante() != null){
//                        print_r('Entered'.$saved_coordinate->getId().'<br><br>');
//                        array_push($listingLocationsToBeSaved, $listingLocation);
//                    }
//                }
//                $status = $listingGeocodeHandler->processLocation($listingLocationsToBeSaved, $saved_coordinate);
//                if(!$status){
                    $count++;
//                    break;
//                }
            }
        }
        fclose($fp);
        return $this->render(
            'CocoricoCoreBundle:Frontend/Listing:import.html.twig',
            array(
                'data' => 'null',
                'count' => $count,
                'status' => 0,
            )
        );
    }

    /**
     * @author Sarthak Patidar
     *
     * Finds if a city exists if not register it
     *
     * @param string $city_name
     * @param string $department_name
     * @param string $area_name
     * @param object $decodeLocation
     *
     * @return Coordinate $saved_coordinate
     */
    private function findCity($city_name, $department_name, $area_name, $decodeLocation){
        $listingGeocodeHandler = $this->get('cocorico.geocode.handler.listing');
        $cities = $this->getDoctrine()->getRepository('CocoricoGeoBundle:City')->findAll();
        $departmentExist = false;
        $areaExist = false;

        foreach ($cities as $city){
            if ($city->getName() == $city_name){
                $coordinate = $this->getDoctrine()->getRepository('CocoricoGeoBundle:Coordinate')->findOneBy(array('city' => $city));
                return $coordinate;
            }
        }

        $country = $this->getDoctrine()->getRepository('CocoricoGeoBundle:Country')->findOneBy(array('id' => 3));
        $departments = $this->getDoctrine()->getRepository('CocoricoGeoBundle:Department')->findAll();
        $areas = $this->getDoctrine()->getRepository('CocoricoGeoBundle:Area')->findAll();

        $newCity = new City();
        $newCity->setCountry($country);

        foreach ($departments as $department){
            if($department->getName() == $department_name){
                $departmentExist = $department;
                break;
            }
        }

        if ($departmentExist){
//            var_dump('Department Exists');
//            print_r('<br>');
//            var_dump($departmentExist->getId());
            $newCity->setDepartment($departmentExist);
            $newCity->setArea($departmentExist->getArea());
        } else{
//            var_dump('New Department Created');
            $newDepartment = new Department();
            $newDepartment->setCountry($country);

            foreach ($areas as $area){
              if($area->getName() == $area_name){
                  $areaExist = $area;
                  break;
              }
            }

            if ($areaExist){
//                var_dump('Area Exists');
//                print_r('<br>');
//                var_dump($areaExist->getId());
                $newDepartment->setArea($areaExist);
                $newCity->setArea($areaExist);
            } else{
//                var_dump('New Area Created');
              $newArea = new Area();
              $newArea->setCountry($country);
              $saved_area =  $listingGeocodeHandler->processArea($newArea, $area_name);
              $newDepartment->setArea($saved_area);
              $newCity->setArea($saved_area);
            }

            $saved_department = $listingGeocodeHandler->processDepartment($newDepartment, $department_name);
            $newCity->setDepartment($saved_department);
        }

        $saved_city = $listingGeocodeHandler->processCity($newCity, $city_name);

        $coordinate = new Coordinate();
        $lat = $decodeLocation->geometry->location->lat;
        $lng = $decodeLocation->geometry->location->lng;
        $coordinate->setLat($lat);
        $coordinate->setLng($lng);
        $coordinate->setCity($saved_city);
        $coordinate->setCountry($saved_city->getCountry());
        $coordinate->setArea($saved_city->getArea());
        $coordinate->setDepartment($saved_city->getDepartment());
        $address = array();

        $place = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lng."&key=AIzaSyA7M_p1ZRJBBv-i1qmmCWkstyQvuZAg7iQ");
//        var_dump($place);
        $decodePlace = JSON_decode($place, true)['results'][0];
//        var_dump($decodePlace);
        $parsedPlace = $decodePlace['address_components'];
        foreach ($parsedPlace as $part){
            foreach ($part['types'] as $type){
                if ($type == 'route'){
                    $coordinate->setRoute($part['long_name']);
                } elseif ($type == 'postal_code'){
                    $coordinate->setZip($part['long_name']);
                }
            }
        }

        $address_string = implode(', ', $address);
        $coordinate->setAddress($address_string);
        $saved_coordinate = $listingGeocodeHandler->processCoordinate($coordinate);

        return $saved_coordinate;
    }
}