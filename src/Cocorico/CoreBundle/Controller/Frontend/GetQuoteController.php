<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 19/6/18
 * Time: 11:58 AM
 */

namespace Cocorico\CoreBundle\Controller\Frontend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Cocorico\UserBundle\Form\Handler\RegistrationFormHandler;


class GetQuoteController extends Controller{

    /**
     * @author Sarthak Patidar
     *
     * Creates a get quote/pyr route.
     *
     * @Route("/get-quote", name="cocorico_get_quote")
     * @Method({"GET"})
     *
     * @param  Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getQuoteAction(Request $request){
        return $this->render('CocoricoCoreBundle:Frontend/GetQuote:getquote.html.twig');
    }
}