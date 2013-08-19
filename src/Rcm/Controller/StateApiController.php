<?php

namespace Rcm\Controller;
use Rcm\Controller\EntityMgrAwareController;


class StateApiController extends EntityMgrAwareController
{
    /**
     * Returns states as JSON. 404's if country not found.
     */
    function listStatesAction()
    {
        $countryIso3 = $this->getEvent()->getRouteMatch()->getParam('country');
        $stateEntities = $this->entityMgr->getRepository('\Rcm\Entity\State')
            ->findBy(
                array('country' => $countryIso3),
                array('name' => 'ASC')
            );

        $states = array();
        foreach ($stateEntities as $state) {
            $name = $state->getName();
            if (empty($name)) {
                $name = $state->getState();
            }
            $states[$state->getState()] = $name;
        }

        if (!count($states)) {
            $httpCode = '404 Not Found';
            header('HTTP/1.0 ' . $httpCode);
            exit($httpCode);
        }

        exit(json_encode($states));
    }
}

