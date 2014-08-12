<?php

namespace Rcm\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Response;
use Rcm\Entity\Site;
use Rcm\Service\PageManager;

class PageSearchApiController extends AbstractRestfulController
{
    function siteTitleSearchAction()
    {
        $query = $this->getEvent()->getRouteMatch()->getParam('query');
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $sm = $this->getServiceLocator()->get(
            'Rcm\Service\SiteManager'
        );
        $siteId = $sm->getCurrentSiteId();
//
//        $this->entityMgr = $this->getServiceLocator()->get(
//            'Doctrine\ORM\EntityManager'
//        );
//        $this->siteInfo = $this->getServiceLocator()->get(
//            'Rcm\Service\SiteManager'
//        );
//        $siteId = $this->siteInfo->getCurrentSiteId();
////        $siteId = $this->siteInfo->getSiteId();

        $results = $em->createQuery(
            '
                        select page.name, pageRevision.pageTitle, page.pageType from Rcm\\Entity\\PageRevision pageRevision
                        join pageRevision.page page
                        join page.site site
                        where (page.name like :query or pageRevision.pageTitle like :query) and site.siteId like :siteId
                    '
        )->setParameter('query', '%' . $query . '%')
            ->setParameter('siteId', '%' . $siteId . '%')
            ->getResult();

        $pageNames = array();
        foreach ($results as $result) {

            $pageNames[$result['name']] = array(
                'title' => $result['pageTitle'],
                'url' => $this->getPageUrl($result['name'], $result['pageType'])
            );
        }

        return new \Zend\View\Model\JsonModel($pageNames);
    }

    function allSitePagesAction()
    {
        $pages = $this->siteInfo->getPages();

        /**@var \Rcm\Entity\Page $page */
        foreach ($pages as $page) {

            if ($page->isTemplate()) {
                continue;
            }

            $pageName = $page->getName();

            $pageUrl = $this->getPageUrl($pageName, $page->getPageType());

            $return[$pageUrl] = $pageName;

        }

        asort($return);

        return new JsonModel($return);
    }


}