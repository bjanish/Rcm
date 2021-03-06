<?php

/**
 * Site Repository
 *
 * This file contains the page repository
 *
 * PHP version 5.3
 *
 * LICENSE: BSD
 *
 * @category  Reliv
 * @package   Rcm
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2014 Reliv International
 * @license   License.txt New BSD License
 * @version   GIT: <git_id>
 * @link      https://github.com/reliv
 */

namespace Rcm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Rcm\Entity\Site as SiteEntity;
use Rcm\Exception\SiteNotFoundException;

/**
 * Site Repository
 *
 * Page Repository.  Used to get custom page results from the DB
 *
 * PHP version 5.3
 *
 * LICENSE: BSD
 *
 * @category  Reliv
 * @package   Rcm
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 * @link      https://github.com/reliv
 */
class Site extends EntityRepository
{
    protected $activeSiteIdCache = [];

    /**
     * Get Site Info
     *
     * @param integer $siteId Site Id
     *
     * @return mixed|null
     */
    public function getSiteInfo($siteId)
    {
        /** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select(
            'partial site.{
                owner,
                theme,
                status,
                favIcon,
                loginPage,
                notAuthorizedPage,
                siteLayout,
                siteTitle,
                siteId
            },
            language,
            country'
        )->from('\Rcm\Entity\Site', 'site')
            ->join('site.country', 'country')
            ->join('site.language', 'language')
            ->where('site.siteId = :siteId')
            ->setParameter('siteId', $siteId);

        try {
            return $queryBuilder->getQuery()->getSingleResult(
                Query::HYDRATE_ARRAY
            );
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * Get All Active Site Objects
     *
     * @param bool $mustBeActive
     *
     * @return array array of site objects
     */
    public function getSites($mustBeActive = false)
    {
        $repo = $this->_em
            ->getRepository('\Rcm\Entity\Site');
        if ($mustBeActive) {
            return $repo->findBy(['status' => 'A']);
        } else {
            return $repo->findAll();
        }
    }

    /**
     * Is Valid Site Id
     *
     * @param integer $siteId      Site Id To Check
     * @param boolean $checkActive Should only check active sites.  Default: true
     *
     * @return boolean
     */
    public function isValidSiteId($siteId, $checkActive = false)
    {
        if (empty($siteId) || !is_numeric($siteId)) {
            return false;
        }

        if ($checkActive && in_array($siteId, $this->activeSiteIdCache)) {
            return true;
        }

        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('site.siteId')
            ->from('\Rcm\Entity\Site', 'site')
            ->where('site.siteId = :siteId')
            ->setParameter('siteId', $siteId);

        if ($checkActive) {
            $queryBuilder->andWhere('site.status = :status');
            $queryBuilder->setParameter('status', 'A');
        }

        $result = $queryBuilder->getQuery()->getScalarResult();

        if (!empty($result)) {
            if ($checkActive) {
                $this->activeSiteIdCache[] = $siteId;
            }
            return true;
        }

        return false;
    }

    public function getSiteWidePluginsList($siteId)
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('partial site.{siteId}, plugins')
            ->from('\Rcm\Entity\Site', 'site')
            ->join('site.sitePlugins', 'plugins')
            ->where('site.siteId = :siteId')
            ->setParameter('siteId', $siteId);

        $result = $queryBuilder->getQuery()->getSingleResult(
            \Doctrine\ORM\Query::HYDRATE_ARRAY
        );

        if (empty($result) || empty($result['sitePlugins'])) {
            return [];
        }

        return $result['sitePlugins'];
    }

    /**
     * Gets site from db by domain name
     *
     * @param $domain
     *
     * @return null|SiteEntity
     */
    public function getSiteByDomain($domain)
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('domain, site, primaryDomain')
            ->from('\Rcm\Entity\Domain', 'domain')
            ->leftJoin('domain.site', 'site')
            ->leftJoin('domain.primaryDomain', 'primaryDomain')
            ->where('domain.domain = :domainName')
            ->setParameter('domainName', $domain);

        try {
            /** @var \Rcm\Entity\Domain $domain */
            $domain = $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }

        if ($domain->getPrimary()) {
            $site = $domain->getPrimary()->getSite();
            $this->_em->detach($site);
            $this->_em->detach($domain);
            $site->setDomain($domain);
        } else {
            $site = $domain->getSite();
        }

        return $site;
    }

    /**
     * Get the Primary Domain for site lookup
     *
     * @param $domain
     *
     * @return null|\Rcm\Entity\Domain
     */
    protected function getPrimaryDomain($domain)
    {
        /** @var \Rcm\Entity\Domain $domain */
        $domain = $this->_em->getRepository('\Rcm\Entity\Domain')
            ->findOneBy(['domain' => $domain]);

        if (empty($domain)) {
            return null;
        }

        $primary = $domain->getPrimary();

        if (!$primary->getPrimary()) {
            return $primary;
        }

        return $this->getPrimaryDomain($primary->getDomainName());
    }

    /**
     * createNewSite
     *
     * @param null $siteId
     *
     * @return SiteEntity
     * @throws SiteNotFoundException
     */
    public function createNewSite($siteId = null)
    {
        if (empty($siteId)) {
            // new site
            /** @var \Rcm\Entity\Site $newSite */
            return new \Rcm\Entity\Site();
        }

        // clone

        /** @var \Rcm\Entity\Site $site */
        $existingSite = $this->find($siteId);

        if (empty($existingSite)) {
            throw new SiteNotFoundException("Site {$siteId} not found.");
        }

        $site = clone($existingSite);

        return $site;
    }

    /**
     * getDoctrine
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getDoctrine()
    {
        return $this->_em;
    }
}
