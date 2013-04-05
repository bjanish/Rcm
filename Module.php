<?php

/**
 * Module Config For ZF2
*
* PHP version 5.3
*
* LICENSE: No License yet
*
* @category  Reliv
* @package   ContentManager\ZF2
* @author    Westin Shafer <wshafer@relivinc.com>
* @copyright 2012 Reliv International
* @license   License.txt New BSD License
* @version   GIT: <git_id>
* @link      http://ci.reliv.com/confluence
*/

namespace Rcm;

use \Zend\ModuleManager\ModuleManager;
use \Zend\Session\SessionManager;
use \Zend\Session\Container;

/**
 * ZF2 Module Config.  Required by ZF2
 *
 * ZF2 reqires a Module.php file to load up all the Module Dependencies.  This
 * file has been included as part of the ZF2 standards.
 *
 * @category  Reliv
 * @package   ContentManager\ZF2
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 * @link      http://ci.reliv.com/confluence
 */
class Module
{

    public function onBootstrap($e)
    {
        $this->bootstrapSession($e);
    }

    public function bootstrapSession($e)
    {
        /** @var \Zend\Session\SessionManager $session  */
        $session = $e->getApplication()
            ->getServiceManager()
            ->get('rcmSesssionManager');

        if (!empty($_GET['sess_id'])) {
            // Set Session ID
            $session->setId($_GET['sess_id']);
            $session->start();

            //Regenerate ID
            $session->regenerateId(true);
            $container = new Container('initialized');
            $container->init = 1;

            //Redirect
            $redirectUrl = $_SERVER['REQUEST_URI'];
            $redirectUrl = str_replace('?sess_id='.$_GET['sess_id'].'&', '?', $redirectUrl);
            $redirectUrl = str_replace('?sess_id='.$_GET['sess_id'], '', $redirectUrl);
            $redirectUrl = str_replace('&sess_id='.$_GET['sess_id'], '', $redirectUrl);

            header('Location: '.$redirectUrl,true,301);
            exit;

        } else {

            //Process normally
            $session->start();
            $container = new Container('initialized');
            if (!isset($container->init)) {
                $session->regenerateId(true);
                $container->init = 1;
            }
        }

        //Logout if requested
        if (!empty($_GET['logout'])) {
            $session->destroy();
        }
    }

    /**
     * getAutoloaderConfig() is a requirement for all Modules in ZF2.  This
     * function is included as part of that standard.  See Docs on ZF2 for more
     * information.
     *
     * @return array Returns array to be used by the ZF2 Module Manager
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * getConfig() is a requirement for all Modules in ZF2.  This
     * function is included as part of that standard.  See Docs on ZF2 for more
     * information.
     *
     * @return array Returns array to be used by the ZF2 Module Manager
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    /**
     * getServiceConfiguration is used by the ZF2 service manager in order
     * to create new objects.
     *
     * @return object Returns an object.
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'cypher'=>function($serviceMgr){
                    $config=$serviceMgr->get('config');
                    $config=$config['encryption']['blockCypher'];
                    $cypher = \Zend\Crypt\BlockCipher::factory(
                        'mcrypt',
                        array('algo' => $config['algo'])
                    );

                    $cypher->setKey($config['key']);
                    return $cypher;
                },
                'rcmSite' => function($serviceMgr){
                    $appConfig = $serviceMgr->get('config');
                    $siteFactory = $serviceMgr->get('Rcm\Model\SiteFactory');

                    //$language = $this->getEvent()->getRouteMatch()->getParam('language');

                    try {
                        $site = $siteFactory->getSite(
                            $_SERVER['HTTP_HOST']//,$language
                        );
                    } catch (\Rcm\Exception\SiteNotFoundException $e) {
                        $site = $siteFactory->getSite(
                            $appConfig['reliv']['defaultDomain']//,$language
                        );
                    }
                    return $site;
                },
                'Rcm\Model\SiteFactory' =>
                function($serviceMgr)
                {
                    $object = new \Rcm\Model\SiteFactory(
                        $serviceMgr->get('em')
                    );
                    return $object;
                },

                'Rcm\Model\PageFactory' =>
                function($serviceMgr)
                {
                    $object = new \Rcm\Model\PageFactory(
                        $serviceMgr->get('em')
                    );
                    return $object;
                },

                'rcmPluginManager' => function($serviceMgr){
                    return new \Rcm\Model\PluginManager(
                        $serviceMgr->get('modulemanager'),
                        $serviceMgr->get('config'),
                        $serviceMgr
                    );
                },

                'rcmUserManager' => function($serviceMgr)
                {
                    $service = new \Rcm\Model\UserManagement\DoctrineUserManager(
                        $serviceMgr->get('cypher')
                    );
                    $service->setEm($serviceMgr->get('em'));
                    return $service;
                },

                'em' => function($serviceMgr){
                    return $serviceMgr->get(
                        'doctrine.entitymanager.ormdefault'
                    );
                },

                'rcmIpInfo' => function(){
                    return new \Rcm\Model\IpInfo();
                },
                'rcmCache' => function($serviceMgr) {
                    $config = $serviceMgr->get('config');

                    $cache = \Zend\Cache\StorageFactory::factory(
                        array(
                            'adapter' => 'filesystem',
                            'plugins' => array(
                                'exception_handler' => array('throw_exceptions' => true),
                                'serializer'
                            ),
                        )
                    );

                   $cache->setOptions(array(
                        'cache_dir' => '/www/sites/reliv/data/cache'
                   ));

                    return $cache;
                },

                'rcmSesssionManager' => function ($sm) {
                    $config = $sm->get('config');
                    if (isset($config['session'])) {
                        $session = $config['session'];

                        $sessionConfig = null;
                        if (isset($session['config'])) {
                            $class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                            $options = isset($session['config']['options']) ? $session['config']['options'] : array();
                            $sessionConfig = new $class();
                            $sessionConfig->setOptions($options);
                        }

                        $sessionStorage = null;
                        if (isset($session['storage'])) {
                            $class = $session['storage'];
                            $sessionStorage = new $class();
                        }

                        $sessionSaveHandler = null;
                        if (isset($session['save_handler'])) {
                            // class should be fetched from service manager since it will require constructor arguments
                            $sessionSaveHandler = $sm->get($session['save_handler']);
                        }

                        $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);

                        if (isset($session['validator'])) {
                            $chain = $sessionManager->getValidatorChain();
                            foreach ($session['validator'] as $validator) {
                                $validator = new $validator();
                                $chain->attach('session.validate', array($validator, 'isValid'));

                            }
                        }
                    } else {
                        $sessionManager = new SessionManager();
                    }
                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                },

            ),
        );
    }

    function getControllerConfig(){
        return array(
            'factories' => array(
                'rcmIndexController' => function($controllerMgr) {
                        $serviceMgr=$controllerMgr->getServiceLocator();
                        $controller = new \Rcm\Controller\IndexController(
                            $serviceMgr->get('rcmUserManager'),
                            $serviceMgr->get('rcmPluginManager'),
                            $serviceMgr->get('em'),
                            $serviceMgr->get('viewRenderer'),
                            $serviceMgr->get('config')
                        );
                    return $controller;
                },
                'rcmAdminController' => function($controllerMgr) {
                    $serviceMgr=$controllerMgr->getServiceLocator();
                    $controller = new \Rcm\Controller\AdminController(
                        $serviceMgr->get('rcmUserManager'),
                        $serviceMgr->get('rcmPluginManager'),
                        $serviceMgr->get('em'),
                        $serviceMgr->get('viewRenderer'),
                        $serviceMgr->get('config')
                    );
                    return $controller;
                },
                'rcmPageSearchApiController' => function($controllerMgr) {
                    $serviceMgr=$controllerMgr->getServiceLocator();
                    $controller = new \Rcm\Controller\PageSearchApiController(
                        $serviceMgr->get('rcmUserManager'),
                        $serviceMgr->get('rcmPluginManager'),
                        $serviceMgr->get('em'),
                        $serviceMgr->get('viewRenderer'),
                        $serviceMgr->get('config')
                    );
                    return $controller;
                },
                'rcmPluginProxyController' => function($controllerMgr) {
                    $serviceMgr=$controllerMgr->getServiceLocator();
                    $controller = new \Rcm\Controller\PluginProxyController(
                        $serviceMgr->get('rcmUserManager'),
                        $serviceMgr->get('rcmPluginManager'),
                        $serviceMgr->get('em'),
                        $serviceMgr->get('viewRenderer'),
                        $serviceMgr->get('config')
                    );
                    return $controller;
                },
                'rcmInstallController' => function($controllerMgr) {
                    $serviceMgr=$controllerMgr->getServiceLocator();
                    $controller =
                        new \Rcm\Controller\InstallController(
                            $serviceMgr->get('em'),
                            $serviceMgr->get('rcmPluginManager')
                        );
                    return $controller;
                },


            )
        );
    }

    /**
     * New Init process for ZF2.
     *
     * @param ModuleManager $moduleManager ZF2 Module Manager.  Passed in
     *                                     from the service manager.
     *
     * @return null
     */

    public function init(\Zend\ModuleManager\ModuleManager $moduleManager)
    {
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        $sharedEvents->attach(
            'Rcm',
            'dispatch',
            array($this, 'baseControllerInit'),
            12
        );

    }

    /**
     * Event Listener for the Base Controller.
     *
     * @param \Zend\EventManager\Event $event ZF2 Called Event
     *
     * @return null
     */
    public function baseControllerInit($event)
    {

        $object = $event->getTarget();

        if ( is_subclass_of(
            $object,
            __NAMESPACE__.'\Controller\BaseController'
        )) {
            $object->init();
        }
    }
}
