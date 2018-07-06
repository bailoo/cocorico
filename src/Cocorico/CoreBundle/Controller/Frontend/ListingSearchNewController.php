<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 12/6/18
 * Time: 6:40 PM
 */


namespace Cocorico\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Entity\ListingCategory;
use Cocorico\CoreBundle\Entity\ListingImage;
use Cocorico\CoreBundle\Event\ListingSearchActionEvent;
use Cocorico\CoreBundle\Event\ListingSearchEvents;
use Cocorico\CoreBundle\Model\ListingSearchRequest;
use Cocorico\CoreBundle\Model\DateRange;
use Cocorico\CoreBundle\Model\ListingLocationSearchRequest;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use http\Env\Response;
use MongoDB\BSON\UTCDateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use DateTime;

class ListingSearchNewController extends Controller
{
    /**
     * Listings search result.
     *
     * @Route("/book-{category}-online/{location}/{eventName}", name="cocorico_listing_search_new_result", requirements={"category"=  "[^/]+"})
     * @Method("GET")
     *
     * @param  Request $request
     * @param mixed $category
     * @param string $location
     * @param string $eventName
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchNewAction(Request $request, $category, $location=null, $eventName = null)
    {
//        $memcache = $this->get('memcache.default');

//        if($memcache->get($category.'.'.$location.'.'.$eventName)){
//            $memcacheData = $memcache->get($category.'-'.$location.'-'.$eventName);
//            print_r($memcache->get($category.'.'.$location.'.'.$eventName));
//        } else{

            /** @var ListingSearchRequest $listingSearchRequest */
            $listingSearchRequest = $this->get('cocorico.listing_search_request');

            $category_id = array();
            $cat = $this->getDoctrine()->getRepository(ListingCategory::class)->findAll();
            if($category == 'anchor'){$category = 'Anchor/Emcee';}
            else if($category == 'band'){$category = 'Live Band';}
            else if($category == 'celebrity'){$category = 'Celebrity Appearance';}
            else if($category == 'photographer'){$category = 'Photo/Videographer';}
            else if($category == 'dancer'){$category = 'Dancer/Troupe';}
            else if ($category == 'variety-artist'){$category = 'Variety Artist';}
            else if ($category == 'makeup-artist'){$category = 'Makeup Artist/Stylist';}
            foreach ($cat as $item){
                if($item->translate()->getName() == ucwords($category)){
                    array_push($category_id,$item->getId());
                    break;
                }
            }
            if(!count($category_id)){
                die("Invalid Category");
            }

            $listingSearchRequest->setCategories($category_id);
            $request->query->add(array('categories' => $category_id));

            /** @var ListingLocationSearchRequest $searchLocation */
            $searchLocation = new ListingLocationSearchRequest($request->getLocale());

            if($location){
                $cookies = $request->cookies;
                $checkCookies = false;

                if($cookies->has('address')) {
                    if ($location == $cookies->get('address')) {
                        $checkCookies = true;
                    }
                }

                if($checkCookies){
                    $location = $cookies->get('address');
                    $addressType = $cookies->get('addressType');
                    $country = $cookies->get('country');
                    $city = $cookies->get('city');
                    $area = $cookies->get('area');
                    $department = $cookies->get('department');
                    $zip = $cookies->get('zip');
                    $lat = $cookies->get('lat');
                    $lng = $cookies->get('lng');
                    $viewport = $cookies->get('viewport');

                    $searchLocation->setCountry($country);
                    $searchLocation->setCity($city);
                    $searchLocation->setArea($area);
                    $searchLocation->setDepartment($department);
                    $searchLocation->setZip($zip);

                } else{
                    $location_json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".$location."&key=AIzaSyA7M_p1ZRJBBv-i1qmmCWkstyQvuZAg7iQ");
                    $parsedLocation = json_decode($location_json, true)['results'][0];

                    $addressType = implode(',', $parsedLocation['types']);
                    $location = str_replace('-',' ', $location);

                    foreach ($parsedLocation['address_components'] as $component){
                        $types = $component['types'];
                        foreach ($types as $type){
                            $value = $component['long_name'];
                            if($type == 'political'){
                                break;
                            } else if($type == 'country'){
                                $searchLocation->setCountry($component['short_name']);
                            } else if($type == 'locality'){
                                $searchLocation->setCity($value);
                            } else if($type == 'administrative_area_level_1'){
                                $searchLocation->setArea($value);
                            } else if($type == 'administrative_area_level_2'){
                                $searchLocation->setDepartment($value);
                            } else if($type == 'postal_code'){
                                $searchLocation->setZip($value);
                            }
                        }
                    }

                    $lat = $parsedLocation['geometry']['location']['lat'];
                    $lng = $parsedLocation['geometry']['location']['lng'];
                    $searchViewport = $parsedLocation['geometry']['viewport'];
                    $viewport = '(('.$searchViewport["southwest"]["lat"].', '.$searchViewport["southwest"]["lng"].'), ('.$searchViewport["northeast"]["lat"].', '.$searchViewport["northeast"]["lng"].'))';
                }

                $searchLocation->setLat($lat);
                $searchLocation->setLng($lng);
                $searchLocation->setAddress(ucwords(strtolower($location)));
                $searchLocation->setAddressType($addressType);
                $searchLocation->setViewport($viewport);

                $request->query->add(
                    array(
                        'location' => array(
                            'address' => $location,
                            'lat' => $searchLocation->getLat(),
                            'lng' => $searchLocation->getLng(),
                            'viewport' => $viewport,
                            'country' => $searchLocation->getCountry(),
                            'area' => $searchLocation->getCountry(),
                            'department' => $searchLocation->getDepartment(),
                            'city' => $searchLocation->getCity(),
                            'zip' => $searchLocation->getZip(),
                            'route' => $searchLocation->getRoute(),
                            'streetNumber' => $searchLocation->getStreetNumber(),
                            'addressType' => $searchLocation->getAddressType(),
                        )
                    )
                );
            } else{
                $viewport = '((-90, -180), (90, 180))';
                $searchLocation->setViewport($viewport);
            }
            $listingSearchRequest->setLocation($searchLocation);

            if($eventName){
                $eventName = str_replace('-', ' ', $eventName);
                $eventName = ucwords($eventName);
                if($eventName == 'Concert Festival'){$eventName = 'Concert/Festival';}
                elseif ($eventName == 'Photo Video Shoot'){$eventName = 'Photo/Video Shoot';}

                $characteristics = $this->getDoctrine()->getRepository('CocoricoCoreBundle:ListingCharacteristic')->findAll();
                foreach ($characteristics as $component){
                    if($component->getName() == 'Event Types'){
                        $characteristic = $component;
                        break;
                    }
                }

                $eventTypes = $this->getDoctrine()->getRepository('CocoricoCoreBundle:ListingCharacteristicValue')->findBy(array('listingCharacteristicType' => $characteristic->getListingCharacteristicType()));
                foreach ($eventTypes as $eventType){
                    if($eventType->getName() == ucfirst($eventName)){
                        $event = $eventType;
                        break;
                    }
                }

                $id = (int)$characteristic->getId();
                $listingCharacteristic = array();
                $listingCharacteristic[$id] = $eventType->getId();
                $listingSearchRequest->setCharacteristics($listingCharacteristic);
//            var_dump($listingSearchRequest->getCharacteristics());
            }

            // searches and returns relevant listings
            $listingSearchRequest->setMaxPerPage(30);
            $results = $this->get("cocorico.listing_search.manager")->search(
                $listingSearchRequest,
                'en'
            );
            $nbListings = $results->count();
            $listings = $results->getIterator();
            $markers = $this->getMarkers($request, $results, $listings);

//        //Persist similar listings id
            $listingSearchRequest->setSimilarListings(array_column(array_column($markers, 0), 'id'));

//        //Persist listing search request in session
            $this->get('session')->set('listing_search_request', $listingSearchRequest);

            $form = $this->createSearchResultForm($listingSearchRequest);


            //Breadcrumbs
            $breadcrumbs = $this->get('cocorico.breadcrumbs_manager');
            $breadcrumbs->addListingResultItems($this->get('request_stack')->getCurrentRequest(), $listingSearchRequest);

            //Add params to view through event listener
            $event = new ListingSearchActionEvent($request);
            $this->get('event_dispatcher')->dispatch(ListingSearchEvents::LISTING_SEARCH_ACTION, $event);
            $extraViewParams = $event->getExtraViewParams();

            if(!$request->query->get('page')){
                $request->query->add(array('page' => '1'));
            }

//        $listingSearchRequest->setPage($request->query->get('page'));
//        var_dump($request->query->get('characteristics['.$id.']'));

            //set memcache
            $memcacheData = array();
            $memcacheData['form'] = $form->createView();
            $memcacheData['$listings'] = $listings;
            $memcacheData['nb_listings'] = $nbListings;
            $memcacheData['markers'] = $markers;
            $memcacheData['listing_search_request'] = $listingSearchRequest;
            $memcacheData['pagination'] = array(
                'page' => $listingSearchRequest->getPage(),
                'pages_count' => ceil($nbListings / $listingSearchRequest->getMaxPerPage()),
                'route' => $request->get('_route'),
                'route_params' => $request->query->all()
            );
            $memcacheData['extraViewParams'] = $extraViewParams;

//            $memcache->set($category.'-'.$location.'-'.$eventName, $memcacheData, 0);

//        }

        return $this->render(
            '@CocoricoCore/Frontend/ListingResult/result.html.twig',
            array_merge(
                array(
                    'form' => $memcacheData['form'],
                    'listings' => $memcacheData['$listings'],
                    'nb_listings' => $memcacheData['nb_listings'],
                    'markers' => $memcacheData['markers'],
                    'listing_search_request' => $listingSearchRequest,
//                    'pagination' => $memcacheData['pagination'],
                    'pagination' => array(
                        'page' => $memcacheData['pagination']['page'],
                        'pages_count' => $memcacheData['pagination']['pages_count'],
                        'route' => $memcacheData['pagination']['route'],
                        'route_params' => $memcacheData['pagination']['route_params']
                    ),
//                    'pagination' => array(
//                        'page' => $listingSearchRequest->getPage(),
//                        'pages_count' => ceil($nbListings / $listingSearchRequest->getMaxPerPage()),
//                        'route' => $request->get('_route'),
//                        'route_params' => $request->query->all()
//                    ),
                ),
                $memcacheData['extraViewParams']
            )
        );

    }

    /**
     * @param  ListingSearchRequest $listingSearchRequest
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createSearchResultForm(ListingSearchRequest $listingSearchRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            'listing_search_result',
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl('cocorico_listing_search_result'),
            )
        );

        return $form;
    }

    /**
     * Get Markers
     *
     * @param  Request        $request
     * @param  Paginator      $results
     * @param  \ArrayIterator $resultsIterator
     * @return array
     */
    protected function getMarkers(Request $request, $results, $resultsIterator)
    {
        //We get listings id of current page to change their marker aspect on the map
        $resultsInPage = array();
        foreach ($resultsIterator as $i => $result) {
            $resultsInPage[] = $result[0]['id'];
        }

        //We need to display all listings (without pagination) of the current search on the map
        $results->getQuery()->setFirstResult(null);
        $results->getQuery()->setMaxResults(null);
        $nbResults = $results->count();

        $imagePath = ListingImage::IMAGE_FOLDER;
        $currentCurrency = $this->get('session')->get('currency', $this->container->getParameter('cocorico.currency'));
        $locale = $request->getLocale();
        $liipCacheManager = $this->get('liip_imagine.cache.manager');
        $currencyExtension = $this->get('lexik_currency.currency_extension');
        $markers = array();

        foreach ($results->getIterator() as $i => $result) {
            $listing = $result[0];

            $imageName = count($listing['images']) ? $listing['images'][0]['name'] : ListingImage::IMAGE_DEFAULT;

            $image = $liipCacheManager->getBrowserPath($imagePath . $imageName, 'listing_medium', array());

            $price = $currencyExtension->convertAndFormat($listing['price'] / 100, $currentCurrency, false);

            $categories = count($listing['listingListingCategories']) ?
                $listing['listingListingCategories'][0]['category']['translations'][$locale]['name'] : '';

            $isInCurrentPage = in_array($listing['id'], $resultsInPage);

            $rating1 = $rating2 = $rating3 = $rating4 = $rating5 = 'hidden';
            if ($listing['averageRating']) {
                $rating1 = ($listing['averageRating'] >= 1) ? '' : 'inactive';
                $rating2 = ($listing['averageRating'] >= 2) ? '' : 'inactive';
                $rating3 = ($listing['averageRating'] >= 3) ? '' : 'inactive';
                $rating4 = ($listing['averageRating'] >= 4) ? '' : 'inactive';
                $rating5 = ($listing['averageRating'] >= 5) ? '' : 'inactive';
            }

            //Allow to group markers with same location
            $locIndex = $listing['location']['coordinate']['lat'] . "-" . $listing['location']['coordinate']['lng'];
            $markers[$locIndex][] = array(
                'id' => $listing['id'],
                'lat' => $listing['location']['coordinate']['lat'],
                'lng' => $listing['location']['coordinate']['lng'],
                'title' => $listing['translations'][$locale]['title'],
                'category' => $categories,
                'image' => $image,
                'rating1' => $rating1,
                'rating2' => $rating2,
                'rating3' => $rating3,
                'rating4' => $rating4,
                'rating5' => $rating5,
                'price' => $price,
                'certified' => $listing['certified'] ? 'certified' : 'hidden',
                'url' => $url = $this->generateUrl(
                    'cocorico_listing_show',
                    array('slug' => $listing['translations'][$locale]['slug'])
                ),
                'zindex' => $isInCurrentPage ? 2 * $nbResults - $i : $i,
                'opacity' => $isInCurrentPage ? 1 : 0.4,

            );
        }

        return $markers;
    }

    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchHomeFormAction()
    {
        $listingSearchRequest = $this->getListingSearchRequest();
        $form = $this->createSearchHomeForm($listingSearchRequest);

        return $this->render(
            '@CocoricoCore/Frontend/Home/form_search.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param  ListingSearchRequest $listingSearchRequest
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createSearchHomeForm(ListingSearchRequest $listingSearchRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            'listing_search_home',
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl('cocorico_listing_search_result'),
            )
        );

        return $form;
    }

    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchFormAction()
    {
        $listingSearchRequest = $this->getListingSearchRequest();
        $form = $this->createSearchForm($listingSearchRequest);

        return $this->render(
            '@CocoricoCore/Frontend/Common/form_search.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param  ListingSearchRequest $listingSearchRequest
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    protected function createSearchForm(ListingSearchRequest $listingSearchRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            'listing_search',
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl('cocorico_listing_search_result'),
            )
        );

        return $form;
    }

    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchResultFormAction()
    {
        $listingSearchRequest = $this->getListingSearchRequest();
        $form = $this->createSearchResultForm($listingSearchRequest);

        return $this->render(
            '@CocoricoCore/Frontend/ListingResult/form_search.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * similarListingAction will list out the listings which are almost similar to what has been
     * searched.
     *
     * @Route("/listing/similar_result/{id}", name="cocorico_listing_similar")
     * @Method("GET")
     *
     * @param  Request $request
     * @param int      $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function similarListingAction(Request $request, $id = null)
    {
        $results = new ArrayCollection();
        $listingSearchRequest = $this->getListingSearchRequest();
        $ids = ($listingSearchRequest) ? $listingSearchRequest->getSimilarListings() : array();
        if ($listingSearchRequest && count($ids) > 0) {
            $results = $this->get("cocorico.listing_search.manager")->getListingsByIds(
                $ids,
                null,
                $request->getLocale(),
                array($id)
            );
        }

        return $this->render(
            '@CocoricoCore/Frontend/Listing/similar_listing.html.twig',
            array(
                'results' => $results
            )
        );
    }

    /**
     * @return ListingSearchRequest
     */
    private function getListingSearchRequest()
    {
        $session = $this->get('session');
        /** @var ListingSearchRequest $listingSearchRequest */
        $listingSearchRequest = $session->has('listing_search_request') ?
            $session->get('listing_search_request') :
            $this->get('cocorico.listing_search_request');

        return $listingSearchRequest;
    }

}