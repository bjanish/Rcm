<?php

namespace Rcm\Controller;

class PageSearchApiController extends  \Rcm\Controller\BaseController
{
    function siteTitleSearchAction(){

        $query = $this->getEvent()->getRouteMatch()->getParam('query');
        $siteId = $this->getEvent()->getRouteMatch()->getParam('siteId');

        $results = $this->entityMgr->createQuery('
            select page.name from Rcm\\Entity\\PageRevision pageRevision
            join pageRevision.page page
            join page.site site
            where site.siteId = :siteId
            and (page.name like :query or pageRevision.pageTitle like :query)
        ')->setParameter('query', '%'.$query.'%')
            ->setParameter('siteId', '%'.$siteId.'%')
            ->getResult();

        return new \Zend\View\Model\JsonModel($results);

    }
}