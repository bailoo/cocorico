<?php

/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 12/6/18
 * Time: 5:41 PM
 */

namespace Cocorico\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Document\ListingAvailability;
use Cocorico\CoreBundle\Entity\ListingCharacteristicValueTranslation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Cocorico\CoreBundle\Entity\ListingListingCategory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Form\Handler\RegistrationFormHandler;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\ListingLocation;
use Cocorico\CoreBundle\Entity\ListingListingCharacteristic;
use Cocorico\CoreBundle\Entity\ListingCharacteristicValue;
use Cocorico\CoreBundle\Entity\ListingPrice;
use Cocorico\CoreBundle\Entity\ListingImage;
use Cocorico\CoreBundle\Entity\ListingBodySpecifications;

/**
 * Listing controller.
 *
 * @Route("/listing")
 */

class ListingImportController extends Controller
{
    /**
     * @author Sarthak Patidar
     *
     * Creates a Listing import entity.
     *
     * @Route("/import", name="cocorico_listing_import")
     * @Method({"GET"})
     *
     * @param  Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function import(Request $request){
        $count = 0;
        $data = array();
        // $fp is file pointer to file listing.csv
        if (($fp = fopen("listing_all.csv", "r")) !== FALSE) {
            while (($row = fgetcsv($fp)) !== FALSE) {
                    //Create new listing and user entity
                    if ($count){
                        $listingImportHandler = $this->get('cocorico.import.handler.listing');
                        $listing = $listingImportHandler->init();
                        $listingPrice = new ListingPrice();
                        $listingLocation = new ListingLocation();
                        $listingBody = new ListingBodySpecifications();
                        $userSet = false;
                        $listingCategories = array();
                        $listingMedia = array();
                        $listingCover = new ListingImage();   //push into media
                        $listingDP = new ListingImage();        //push into media
                        $listingGallery = array();                      //push into media
                        $listingAudio = array();                        //push into media
                        $listingVideo = array();                         //push into media
                        $category = array();             //push in $listingCategories

                        $checkUserExists = $this->getDoctrine()->getRepository('CocoricoUserBundle:User')->findOneBy(array('email' => $row[3]));
                        if($checkUserExists) {
                            $userSet = true;
                            $user = $checkUserExists;
                        } else{
                            $user = new User();

                            $user->setFirstName($row[1]);
                            $user->setPhone($row[2]);
                            $email = $row[3];
                            $user->setNationality('IN');
                            $user->setCountryOfResidence('IN');
                            $user->setPhonePrefix('+91');
                            $user->setUsername($email);
                            $user->setEmail($email);
                            $user->setPlainPassword('starclinch');
                        }
                        $status = "Insert ";

                        // populating listing entity
                        $listing->setOldId($row[0]);

                        $listingCover->setName($row[4]);
                        $listingCover->setPosition(2);
                        $listingMedia['cover'] = $listingCover;

                        $listing->setTitle($row[5]);

                        $categoryName = $row[6];
                        $categoryId = $this->getDoctrine()->getRepository('CocoricoCoreBundle:ListingCategoryTranslation')->findOneBy(array('name' => $categoryName))->getCategoryId();
                        array_push($listingCategories,$categoryId);
                        $category['listingListingCategories'] = $listingCategories;
                        $listing = $listingImportHandler->addImportCategory($listing, $category);

                        $listingLocation->setCity($row[7]);
                        $listing->setLocation($listingLocation);

                        $listing->setAverageRating($row[8]);
                        $listing->setRateCount($row[9]);
                        $listing->setCommentCount($row[10]);
                        $listing->setLikes($row[11]);
                        $listing->setUsp($row[12]);

                        $listingDP->setName($row[13]);
                        $listingDP->setPosition(1);
                        $listingMedia['dp'] = $listingDP;

                        $listing->SetDescription($row[14]);

                        $gallery = explode(', ',$row[15]);
                        unset($gallery[0]);
//                        $listingImage = new ListingImage();
                        $pos = 3;
                        foreach ($gallery as $image){
                            $listingGalleryImage = new ListingImage();
                            $listingGalleryImage->setName($image);
                            $listingGalleryImage->setPosition($pos);
                            array_push($listingGallery, $listingGalleryImage);
                            $pos++;
                        }
                        $listingMedia['gallery'] = $listingGallery;

                        if($row[16]){
                            $listingBody->setHeight($row[16]);
                        }
                        if($row[17]){
                            $listingBody->setBust($row[17]);
                        }
                        if($row[18]){
                            $listingBody->setWeight($row[18]);
                        }
                        if($row[19]){
                            $listingBody->setHips($row[19]);
                        }
                        if($row[20]){
                            $listingBody->setEyeColor($row[20]);
                        }
                        if($row[21]){
                            $listingBody->setSkinColor($row[21]);
                        }
                        if($row[22]){
                            $listingBody->setTatooType($row[22]);
                        }
                        if($row[23]){
                            $listingBody->setPiercingType($row[23]);
                        }

                        if($row[24]){
                            $listing->setVocalGender($row[24]);
                        }

                        if($row[25]){
                            $audio = explode(',', $row[25]);
                            foreach ($audio as $item){
                                $listingAudioItem = new ListingImage();
                                $listingAudioItem->setType('audio');
                                $listingAudioItem->setName($item);
                                array_push($listingAudio, $listingAudioItem);
                            }
                            $listingMedia['audio'] = $listingAudio;
                        }

                        if($row[26]){
                            $video = explode(', ', $row[26]);
                            $numberVideo = 0;
                            foreach ($video as $item){
                                $listingVideoItem = new ListingImage();
                                $listingVideoItem->setType('video');
                                $listingVideoItem->setName($item);
                                array_push($listingVideo, $listingVideoItem);
                                $numberVideo++;
                            }
                            $listingMedia['video'] = $listingVideo;
                        }

                        $listing->setLanguages($row[27]);
                        $listing->setPerformingMembers($row[28]);
                        $listing->setOffStageTeam($row[29]);
                        $listing->setTravel($row[30]);

                        $duration = explode('-', $row[31]);
                        $listing->setMinDuration($duration[0]);
                        if(count($duration) > 1){
                            $listing->setMaxDuration($duration[1]);
                        }

                        $listingPrice->setPriceCampus($row[32]);
                        $listingPrice->setPriceCharity($row[32]);
                        $listingPrice->setPriceConcertFestival($row[33]);
                        $listingPrice->setPriceCorporate($row[33]);
                        $listingPrice->setPriceExhibition($row[32]);
                        $listingPrice->setPriceFashionShow($row[32]);
                        $listingPrice->setPriceInauguration($row[32]);
                        $listingPrice->setPriceKidsParty($row[33]);
                        $listingPrice->setPricePhotoVideoshoot($row[33]);
                        $listingPrice->setPricePrivateParty($row[33]);
                        $listingPrice->setPriceProfessionalHiring($row[33]);
                        $listingPrice->setPriceReligious($row[33]);
                        $listingPrice->setPriceRestaurent($row[32]);
                        $listingPrice->setPriceWedding($row[33]);

                        $listing->setPrice($row[33]);
                        $listing->setGender($row[34]);

                        $subCategories = explode(', ', $row[36]);
                        $characteristic = $this->getDoctrine()->getRepository('CocoricoCoreBundle:ListingCharacteristicTranslation')->findOneBy(array('name' => 'Sub Category'))->getTranslatableId();
                        $listingSubCategories = array();
                        $typeId = $this->getDoctrine()->getRepository('CocoricoCoreBundle:ListingCharacteristicType')->findOneBy(array('name' => 'SubCategory'));
                        $countValue = count($this->getDoctrine()->getRepository('CocoricoCoreBundle:ListingCharacteristicValue')->findAll())-13;
                        foreach ($subCategories as $subCategory){
                            $instance = array();
                            $subCategoryCharacteristic = new ListingListingCharacteristic();
                            $subCategoryObject = $this->getDoctrine()->getRepository('CocoricoCoreBundle:ListingCharacteristicValueTranslation')->findOneBy(array('name' => $subCategory));
                            if($subCategoryObject){
                                $instance['exists'] = true;
                                $subCategoryCharacteristic->setListingCharacteristicValue($subCategoryObject->getTranslatableId());
                            } else{
                                $instance['exists'] = false;
                                $listingCharacteristicValue = new ListingCharacteristicValue();
                                $listingCharacteristicValue->setPosition($countValue);
                                $listingCharacteristicValue->setListingCharacteristicType($typeId);
                                $countValue++;
                                $instance['value'] = $listingCharacteristicValue;
                                $instance['translation'] = $subCategory;
                            }
                            $subCategoryCharacteristic->setListingCharacteristic($characteristic);
                            $instance['subCategory'] = $subCategoryCharacteristic;
                           array_push($listingSubCategories, $instance);
                        }

                        $listingEventTypes= explode(', ', $row[37]);
                        $characteristic = $this->getDoctrine()->getRepository('CocoricoCoreBundle:ListingCharacteristicTranslation')->findOneBy(array('name' => 'Event Types'))->getTranslatableId();
                        $listingEvents = array();
                        foreach ($listingEventTypes as $event){
                            $eventCharacteristic = new ListingListingCharacteristic();
                            $eventId = $this->getDoctrine()->getRepository('CocoricoCoreBundle:ListingCharacteristicValueTranslation')->findOneBy(array('name' => $event))->getTranslatableId();
                            $eventCharacteristic->setListingCharacteristic($characteristic);
                            $eventCharacteristic->setListingCharacteristicValue($eventId);
                            array_push($listingEvents, $eventCharacteristic);
                        }

                        $listing->setSlug($row[38]);
                        $listing->setFbId($row[39]);
                        $listing->setTags($row[40]);

                        //Import Function
                        $success = $listingImportHandler->processImport($listing, $user,$listingLocation, $listingBody, $listingPrice, $listingMedia, $listingEvents, $listingSubCategories, $userSet);
                        if(!$success){
                            $status .= "Break at ".$row[0];
                            break;
                        }else{
                            $status .= "Push";
                        }
                        array_push($data, $row);
                        if($count > 115){
                            break;
                        }
                    }
                $count++;
            }
            fclose($fp);
        }
        return $this->render(
            'CocoricoCoreBundle:Frontend/Listing:import.html.twig',
            array(
                'data' => 'null',
                'count' => $count,
                'status' => $status,
            )
        );
    }
}