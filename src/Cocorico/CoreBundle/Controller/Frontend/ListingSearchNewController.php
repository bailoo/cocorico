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
     * @Route("/artist/{category}/{location}/{date}", name="cocorico_listing_search_new_result")
     * @Method("GET")
     *
     * @param  Request $request
     * @param string $category
     * @param string $location
     * @param string $date
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchNewAction(Request $request, $category, $location=null, $date = null)
    {
        $markers = array('listingsIds' => array(), 'markers' => array());
        $listings = new \ArrayIterator();
        $nbListings = 0;

        /** @var ListingSearchRequest $listingSearchRequest */
        $listingSearchRequest = $this->get('cocorico.listing_search_request');
        $category_id = array();
        $cat = $this->getDoctrine()->getRepository(ListingCategory::class)->findAll();
        foreach ($cat as $item){
            if($item->translate()->getName() == ucfirst($category)){
                array_push($category_id,$item->getId());
                break;
            }
        }
        if(!count($category_id)){
            die("Invalid Category");
        }
        $listingSearchRequest->setCategories($category_id);

        /** @var ListingLocationSearchRequest $searchLocation */
        $searchLocation = new ListingLocationSearchRequest($request->getLocale());

        if($location){
            $location_json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".$location."&key=AIzaSyA7M_p1ZRJBBv-i1qmmCWkstyQvuZAg7iQ");
            $parsedLocation = json_decode($location_json, true)['results'][0];

            $addressType = implode(',', $parsedLocation['types']);
            $searchLocation->setAddressType($addressType);
//            $location = explode('-',$location);
            $location = str_replace('-',' ', $location);
            $searchLocation->setAddress(ucwords(strtolower($location)));

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

            $searchLocation->setLat($parsedLocation['geometry']['location']['lat']);
            $searchLocation->setLng($parsedLocation['geometry']['location']['lng']);
            $searchViewport = $parsedLocation['geometry']['viewport'];
            $viewport = '(('.$searchViewport["southwest"]["lat"].', '.$searchViewport["southwest"]["lng"].'), ('.$searchViewport["northeast"]["lat"].', '.$searchViewport["northeast"]["lng"].'))';
            $searchLocation->setViewport($viewport);
        } else{
            $viewport = '((-90, -180), (90, 180))';
            $searchLocation->setViewport($viewport);
        }
        $listingSearchRequest->setLocation($searchLocation);

        if($date){
            $dateRange = new DateRange();
            $start = new DateTime($date);
            $dateRange->setStart($start);
            var_dump($dateRange);
        }

        // searches and returns relevant listings
        $results = $this->get("cocorico.listing_search.manager")->search(
            $listingSearchRequest,
            $request->getLocale()
        );
        $nbListings = $results->count();
        $listings = $results->getIterator();
        $markers = $this->getMarkers($request, $results, $listings);

//        //Persist similar listings id
        $listingSearchRequest->setSimilarListings(array_column(array_column($markers, 0), 'id'));

//        //Persist listing search request in session
        $this->get('session')->set('listing_search_request', $listingSearchRequest);

        //Create Form to Render//Cocorico\CoreBundle\Entity\ListingCategory Object (
    // [id:Cocorico\CoreBundle\Entity\ListingCategory:private] =>
    // [parent:Cocorico\CoreBundle\Entity\ListingCategory:private] =>
    // [children:Cocorico\CoreBundle\Entity\ListingCategory:private] =>
    // [listingListingCategories:Cocorico\CoreBundle\Entity\ListingCategory:private] => Doctrine\Common\Collections\ArrayCollection Object (
    //      [elements:Doctrine\Common\Collections\ArrayCollection:private] => Array ( )
    // )
    // [fields:Cocorico\CoreBundle\Entity\ListingCategory:private] => Doctrine\Common\Collections\ArrayCollection Object (
    //      [elements:Doctrine\Common\Collections\ArrayCollection:private] => Array ( )
    // )
    // [lft:protected] =>
    // [lvl:protected] =>
    // [rgt:protected] =>
    // [root:protected] =>
    // [translations:protected] =>
    // [newTranslations:protected] =>
    // [currentLocale:protected] =>
    // [defaultLocale:protected] => en
// )
        $form = $this->createSearchResultForm($listingSearchRequest);


        //Breadcrumbs
        $breadcrumbs = $this->get('cocorico.breadcrumbs_manager');
        $breadcrumbs->addListingResultItems($this->get('request_stack')->getCurrentRequest(), $listingSearchRequest);

        //Add params to view through event listener
        $event = new ListingSearchActionEvent($request);
        $this->get('event_dispatcher')->dispatch(ListingSearchEvents::LISTING_SEARCH_ACTION, $event);
        $extraViewParams = $event->getExtraViewParams();

        return $this->render(
            '@CocoricoCore/Frontend/ListingResult/result.html.twig',
            array_merge(
                array(
                    'form' => $form->createView(),
                    'listings' => $listings,
                    'nb_listings' => $nbListings,
                    'markers' => $markers,
                    'listing_search_request' => $listingSearchRequest,
                    'pagination' => array(
                        'page' => $listingSearchRequest->getPage(),
                        'pages_count' => ceil($nbListings / $listingSearchRequest->getMaxPerPage()),
                        'route' => $request->get('_route'),
                        'route_params' => $request->query->all()
                    ),
                ),
                $extraViewParams
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