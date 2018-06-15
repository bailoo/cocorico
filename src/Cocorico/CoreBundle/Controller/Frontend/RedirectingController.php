<?php
/**
 * Created by PhpStorm.
 * User: sarthak
 * Date: 15/6/18
 * Time: 6:21 PM
 */

namespace Cocorico\CoreBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class RedirectingController extends Controller
{
    /**
     * @param Request $request
     * @Route("/{url}", name="remove_trailing_slash",
     *     requirements={"url" = ".*\/$"})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeTrailingSlashAction(Request $request)
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        return $this->redirect($url, 301);
    }
}