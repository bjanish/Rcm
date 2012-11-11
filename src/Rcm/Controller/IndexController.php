<?php
/**
 * Index Controller for the entire application
 *
 * This file contains the main controller used for the application.  This
 * should extend from the base class and should need no further modification.
 *
 * PHP version 5.3
 *
 * LICENSE: No License yet
 *
 * @category  Reliv
 * @package   Main\Application\Controllers\Index
 * @author    Unkown <unknown@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   GIT: <git_id>
 * @link      http://ci.reliv.com/confluence
 */
namespace Rcm\Controller;

use Rcm\Entity\PageRevision,
\Rcm\Entity\PluginInstance;

/**
 * Index Controller for the entire application
 *
 * This is main controller used for the application.  This should extend from
 * the base class located in Rcm and should need no further
 * modification.
 *
 * @category  Reliv
 * @package   Main\Application\Controllers\Index
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 * @link      http://ci.reliv.com/confluence
 *
 */
class IndexController extends \Rcm\Controller\BaseController
{
    /**
     * @var \Rcm\Entity\Page
     */
    protected $page;

    /**
     * @var \Rcm\Entity\PageRevision
     */
    protected $pageRevision;

    protected $pageRevIsDirty = false;

    protected $pluginCount = 0;

    /**
     * Index Action - This is the base action that all page requests
     * that come through the content manager use.  This action will check
     * that the page exists, pull in all the needed plugin instances,
     * check to see if the admin screens should show, and finally pass control
     * to the correct view models.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $plugins = array();
        $pageName = $this->getEvent()->getRouteMatch()->getParam('page');
        $pageRevisionId= $this->getEvent()->getRouteMatch()->getParam('revision');

        if (empty($pageName)) {
            $pageName = 'index';
        }

        if ($this->adminIsLoggedIn()) {
            $this->page = $this->siteInfo->getPageByName($pageName, true);
        } else {
            $this->page = $this->siteInfo->getPageByName($pageName);
        }

        if (!$this->page) {
            $this->response->setStatusCode(404);
            return $this->view;
        }

        /**
         *   If Admin we're going to check for a staged revision.
         */

        if ($this->adminIsLoggedIn() && empty($pageRevisionId)) {
            $this->pageRevision = $this->page->getStagedRevision();

            //If no stage exists return published
            if (empty($this->pageRevision)) {
                $this->pageRevision = $this->page->getPublishedRevision();
            }

        } elseif (empty($pageRevisionId)) {
            $this->pageRevision = $this->page->getPublishedRevision();
        } else {
            $this->pageRevision = $this->page->getRevisionById($pageRevisionId);
        }

        if (!$this->pageRevision) {
            $this->response->setStatusCode(404);
            return $this->view;
        }

        $pageInstances = $this->pageRevision->getInstancesForDisplay();

        if (!empty($pageInstances)) {
            foreach ($pageInstances as $container => $ordered) {
                /** @var \Rcm\Entity\PagePluginInstance $instance */
                foreach ($ordered as $order => $instance) {
                    $plugins[$container][$order] = $this
                        ->pluginManager->prepPluginInstance(
                            $instance->getInstance(),
                            $this->getEvent()
                        );
                }
            }
        }

        $layoutView = $this->layout();

        $layoutTemplatePath = $this->getLayout();

        $layoutView->setTemplate('layout/'.$layoutTemplatePath);
        $layoutView->setVariable('plugins', $plugins);

        /** @var \Zend\Mvc\Controller\Plugin\Layout $layoutView  */

        $layoutView->setVariable('metaTitle', $this->pageRevision->getPageTitle());
        $layoutView->setVariable('metaDesc', $this->pageRevision->getDescription());
        $layoutView->setVariable('metaKeys', $this->pageRevision->getKeywords());

        if ($this->adminIsLoggedIn()) {
            $this->doAdmin();
        }

        return $this->view;
    }

    /**
     * Admin Init method.  This method will process all the needed items
     * that must be preformed for the admin screen to show up correctly.
     * When complete will pass back a completed admin view layer for display.
     * This is used by the Index Action when an admin user is encountered.
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function doAdmin()
    {
        $layout = $this->layout();

        $layout->setVariable('adminIsLoggedIn', $this->adminIsLoggedIn());

        $layout->setVariable('rcmAdminMode', true);

        $layout->setVariable(
            'adminPanel',
            $this->setupAdminToolBar()
        );

        $layout->setVariable(
            'page',
            $this->page
        );

        $layout->setVariable(
            'language',
            $this->siteInfo->getLanguage()->getLanguage()
        );

        $layout->setVariable(
            'pageRevision',
            $this->pageRevision->getPageRevId()
        );

        $layout->setVariable(
            'layoutContainers',
            $this->getLayoutEditorContents()
        );

        $layout->setVariable(
            'newPageLayoutContainers',
            $this->getPageLayoutsForNewPages()
        );

        $layout->setVariable(
            'rcmTemplates',
            $this->siteInfo->getTemplates()
        );

        $layout->setVariable('newPluginCount', --$this->pluginCount);
        $layout->setVariable(
            'adminRichEditor',
            $this->config['reliv']['adminRichEditor']
        );
    }

    /**
     * Get Layout method will attempt to locate and fetch the correct layout
     * for the site and page.  If found it will pass back the path to correct
     * view template so that the indexAction can pass that value on to the
     * renderer.
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected  function getLayout()
    {
        //Get Page Layout
        $config = $this->config;
        $layout = $this->pageRevision->getPageLayout();
        $theme = $this->siteInfo->getTheme();

        if (!empty($config['Rcm']['themes'][$theme]['layouts'][$layout]['file'])) {
            return $config['Rcm']['themes'][$theme]['layouts'][$layout]['file'];
        } elseif (!empty($config['Rcm']['themes'][$theme]['layouts']['default']['file'])) {
            return $config['Rcm']['themes'][$theme]['layouts']['default']['file'];
        } elseif (!empty($config['Rcm']['themes']['generic']['layouts'][$layout]['file'])) {
            return $config['Rcm']['themes']['generic']['layouts'][$layout]['file'];
        } elseif (
            !empty($config['Rcm']['themes']['generic']['layouts']['default']['file'])
        ) {
            return $config['Rcm']['themes']['generic']['layouts']['default']['file'];
        } else {
            throw new \InvalidArgumentException('No Layouts Found in config');
        }
    }

    /**
     * Gets all the views available for the site/domain for use with the
     * create new page option in the admin section.
     *
     * @return mixed
     */
    protected function getPageLayoutsForNewPages()
    {
        $config = $this->config;
        $domain = $this->siteInfo->getDomain()->getDomainName();

        if (!empty($config['reliv']['layouts'][$domain])) {
            return $config['reliv']['layouts'][$domain];
        } else {
            return $config['reliv']['layouts']['default'];
        }
    }

    /**
     * Get a new instance of all plugins for use with the layout editor
     *
     * @return array
     */
    protected function getLayoutEditorContents()
    {
        $return = $this->getPlugins();
        $return['siteWide'] = $this->getSiteWidePlugins();
        return $return;
    }

    /**
     * Get a new instance of all plugins
     *
     * @return array
     */
    protected function getPlugins()
    {
        $return = array();

        if (empty($this->config['rcmPlugin'])) {
            return false;
        }

        foreach ($this->config['rcmPlugin'] as $pluginName => $plugin) {
            if (empty($plugin['type'])) {
                continue;
            }

            --$this->pluginCount;
            $instance = new \Rcm\Entity\PluginInstance();
            $instance->setPlugin($pluginName);
            $instance->setInstanceId($this->pluginCount);
            $this->pluginManager->prepPluginInstance($instance, $this->getEvent());

            $return[$plugin['type']][$pluginName] = $instance;
        }

        return $return;
    }

    /**
     * Get an instance of all site wide plugins for current site.
     *
     * @return array
     */
    protected function getSiteWidePlugins()
    {
        $return = array();
        $siteWideInstances = $this->siteInfo->getSiteWidePlugins();

        /** @var \Rcm\Entity\PluginInstance $instance */
        foreach ($siteWideInstances as $instance) {
            $instanceCheck = $this->pageRevision->getInstanceById(
                $instance->getInstanceId()
            );

            if (!empty($instanceCheck)) {
                $instance->setOnPage(true);
            } else {
                $this->pluginManager->prepPluginInstance($instance, $this->getEvent());
            }

            $return[] = $instance;
        }

        return $return;
    }

    /**
     * Get all page revisions and place them into the correct menu items
     * for the admin tool bar in the content manager.
     *
     * @return mixed
     */
    protected function setupAdminToolBar()
    {
        $adminPanel = $this->config['reliv']['adminPanel'];
        $revisionLinks = $this->getPageRevisionLinks();

        $adminPanel['Page']['links']['Restore']['links']
            = $revisionLinks['restore']['menu'];
        $adminPanel['Page']['links']['Drafts']['links']
            = $revisionLinks['drafts']['menu'];

        $adminPanel['Page']['links']['Publish']['links']['Publish Now']['href']
            = $this->getPublishLink($this->pageRevision->getPageRevId());

        $adminPanel['Page']['links']['Publish']['links']['Stage']['href']
            = $this->getStagingLink($this->pageRevision->getPageRevId());

        return $adminPanel;
    }

    /**
     * Get the link to the published revision
     *
     * @param int $revisionId Published Revision ID
     *
     * @return mixed
     */
    protected function getPublishLink($revisionId)
    {
        return $this->getLink('contentManagerPublish', $revisionId);
    }

    /**
     * Get the link to the staged revision
     *
     * @param int $revisionId Staged Revision ID
     *
     * @return mixed
     */
    protected function getStagingLink($revisionId)
    {
        return $this->getLink('contentManagerStage', $revisionId);
    }

    /**
     * Get a valid link to a page revision.
     *
     * @param string $type       Type of link requested
     * @param int    $revisionId Revision ID number
     *
     * @return mixed
     */
    protected function getLink($type, $revisionId)
    {
        return $this->url()->fromRoute(
            $type,
            array(
                'page' => $this->page->getName(),
                'language' => $this->siteInfo->getLanguage()->getLanguage(),
                'revision' => $revisionId
            )
        );
    }

    /**
     * @return array
     */
    protected function getPageRevisionLinks()
    {
        /** @var \RCM\Entity\PageRevision $revision */
        foreach ($this->page->getRevisions() as $revision) {

            $linkDisplay
                = $revision->getCreatedDate()->format('Y-m-d');
            $linkDisplay .= ' - '.$revision->getAuthor();

            $linkHref = $this->getLink(
                'contentManager',
                $revision->getPageRevId()
            );

            $dateForSort = $revision->getCreatedDate()->format('Ymd');

            if ($revision->isStaged()) {
                continue;
            }

            if ($revision->wasPublished()
                && $this->page->getCurrentRevision()->getPageRevId() != $revision->getPageRevId()
            ) {
                $restoreLinks[$revision->getPageRevId()] = array(
                    'display' => $linkDisplay,
                    'aclGroups' => 'admin',
                    'cssClass' => 'restoreInstanceIcon',
                    'href' => $this->getPublishLink($revision->getPageRevId()),
                );
            } elseif (!$revision->wasPublished()) {
                $draftLinks[$revision->getPageRevId()] = array(
                    'display' => $linkDisplay,
                    'aclGroups' => 'admin',
                    'cssClass' => 'restoreInstanceIcon',
                    'href' => $linkHref,
                );
            }
        }

        $links = array(
            'restore' => array(
                'menu' => array(),
                'others' => array(),
            ),
            'drafts' => array(
                'menu' => array(),
                'others' => array(),
            )
        );

        if (!empty($restoreLinks)) {
            krsort($restoreLinks);
            $links['restore']['menu'] = array_slice($restoreLinks, 0, 10);
            $links['restore']['others'] = array_slice($restoreLinks, 9);
        }

        if (!empty($draftLinks)) {
            krsort($draftLinks);
            $links['drafts']['menu'] = array_slice($draftLinks, 0, 10);
            $links['drafts']['others'] = array_slice($draftLinks, 9);
        }

        return $links;
    }

    protected function getTemplates() {
        $em = $this->entityMgr;
        $repo = $em->getRepository('\Rcm\Entity\Page');

        $templates = $repo->findBy(
            array(
                ''
            )
        );
    }
}
