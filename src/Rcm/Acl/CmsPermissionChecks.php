<?php

namespace Rcm\Acl;

use RcmUser\Service\RcmUserService;

class CmsPermissionChecks
{
    /** @var  \RcmUser\Service\RcmUserService */
    protected $rcmUserService;

    public function __construct(RcmUserService $rcmUserService)
    {
        $this->rcmUserService = $rcmUserService;
    }

    /**
     * Check to make sure user can see revisions
     *
     * @return bool
     */
    public function shouldShowRevisions($siteId, $pageType, $pageName)
    {
        $allowedRevisions = $this->rcmUserService->isAllowed(
            'sites.' . $siteId . '.pages.' . $pageType . '.' . $pageName,
            'edit',
            'Rcm\Acl\ResourceProvider'
        );

        if ($allowedRevisions) {
            return true;
        }

        $allowedRevisions = $this->rcmUserService->isAllowed(
            'sites.' . $siteId . '.pages.' . $pageType . '.' . $pageName,
            'approve',
            'Rcm\Acl\ResourceProvider'
        );

        if ($allowedRevisions) {
            return true;
        }

        $allowedRevisions = $this->rcmUserService->isAllowed(
            'sites.' . $siteId . '.pages.' . $pageType . '.' . $pageName,
            'revisions',
            'Rcm\Acl\ResourceProvider'
        );

        if ($allowedRevisions) {
            return true;
        }

        $allowedRevisions = $this->rcmUserService->isAllowed(
            'sites.' . $siteId . '.pages',
            'create',
            'Rcm\Acl\ResourceProvider'
        );

        if ($allowedRevisions) {
            return true;
        }

        return false;
    }
}