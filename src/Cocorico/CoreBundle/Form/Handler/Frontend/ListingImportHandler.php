<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 12/6/18
 * Time: 6:13 PM
 */

namespace Cocorico\CoreBundle\Form\Handler\Frontend;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\ListingBodySpecifications;
use Cocorico\CoreBundle\Entity\ListingLocation;
use Cocorico\CoreBundle\Entity\ListingPrice;
use Cocorico\CoreBundle\Model\Manager\ListingImportManager;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Form\Handler\RegistrationFormHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handle Listing Import
 *
 */
class ListingImportHandler
{
    protected $request;
    protected $listingImportManager;
    protected $registrationHandler;

    /**
     * @param RequestStack $requestStack
     * @param ListingImportManager $listingImportManager
     * @param RegistrationFormHandler $registrationHandler
     */
    public function __construct(RequestStack $requestStack, ListingImportManager $listingImportManager, RegistrationFormHandler $registrationHandler) {
        $this->request = $requestStack->getCurrentRequest();
        $this->listingImportManager = $listingImportManager;
        $this->registrationHandler = $registrationHandler;
    }

    /**
     * @return Listing
     */
    public function init()
    {
        $listing = new Listing();
        return $listing;
    }

    /**
     * @author Sarthak Patidar
     *
     * Process Listing
     *
     * @param Listing $listing
     * @param User $user
     * @param ListingLocation $listingLocation
     * @param ListingBodySpecifications $listingBody
     * @param ListingPrice $listingPrice
     * @param array $listingMedia
     * @param array $eventTypes
     * @param array $subcategory
     * @param bool $userSet
     *
     * @return string
     */
    public function processImport(Listing $listing, User $user, ListingLocation $listingLocation, ListingBodySpecifications $listingBody, ListingPrice $listingPrice, $listingMedia, $eventTypes, $subcategory, $userSet)
    {
        return $this->importListing($listing, $user,$listingLocation, $listingBody, $listingPrice, $listingMedia, $eventTypes, $subcategory, $userSet);
    }

    /**
     * @author Sarthak Patidar
     *
     * @param Listing $listing
     * @param User $user
     * @param ListingLocation $listingLocation
     * @param ListingBodySpecifications $listingBody
     * @param ListingPrice $listingPrice
     * @param array $listingMedia
     * @param array $listingEvents
     * @param array $listingSubCategories
     * @param bool $userSet
     *
     * @return string|boolean
     *
     */
    private function importListing(Listing $listing, User $user, ListingLocation $listingLocation, ListingBodySpecifications $listingBody, ListingPrice $listingPrice, $listingMedia, $listingEvents, $listingSubCategories, $userSet)
    {
        if(!$userSet){
            $this->registrationHandler->handleRegistration($user);
        }
        if($user->getId()){
            $listing->setUser($user);
            $listing->setLocation($listingLocation);
            $this->listingImportManager->save($listing);
            if($listing->getId() != NULL){
                $this->listingImportManager->saveListingMedia($listingMedia,$listing);
                $this->listingImportManager->saveListingPrice($listingPrice,$listing);
                $this->listingImportManager->saveListingBodySpecifications($listingBody,$listing);
                $this->listingImportManager->saveListingEvents($listingEvents,$listing);
                $this->listingImportManager->saveListingSubCategory($listingSubCategories,$listing);
                return true;
            }
        }
        return false;
    }

    /**
     * @author Sarthak Patidar
     *
     * Add Category to Listing Import
     *
     * @param Listing $listing
     * @param array $categories
     *
     * @return Listing;
     */
    public function addImportCategory(Listing $listing, $categories){

        $listingCategories = $categories["listingListingCategories"];
        $listingCategoriesValues = isset($categories["categoriesFieldsSearchableValuesOrderedByGroup"]) ? $categories["categoriesFieldsSearchableValuesOrderedByGroup"] : array();

        if ($categories) {
            $listing = $this->listingImportManager->addCategories(
                $listing,
                $listingCategories,
                $listingCategoriesValues
            );
        }

        return $listing;
    }
}
