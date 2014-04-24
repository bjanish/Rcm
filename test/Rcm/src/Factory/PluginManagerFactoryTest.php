<?php
/**
 * Test for Factory PluginManagerFactory
 *
 * This file contains the test for the PluginManagerFactory.
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
 * @link      http://github.com/reliv
 */

namespace RcmTest\Factory;

require_once __DIR__ . '/../../../Base/BaseTestCase.php';

use Rcm\Service\PluginManager;
use Rcm\Factory\PluginManagerFactory;
use RcmTest\Base\BaseTestCase;
use Zend\ServiceManager\ServiceManager;

/**
 * Test for Factory PluginManagerFactory
 *
 * Test for Factory PluginManagerFactory
 *
 * @category  Reliv
 * @package   Rcm
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 * @link      http://github.com/reliv
 *
 */
class PluginManagerFactoryTest extends BaseTestCase
{
    /**
     * Setup for tests
     *
     * @return null
     */
    public function setUp()
    {
        $this->addModule('Rcm');
        parent::setUp();
    }

    /**
     * Generic test for the constructor
     *
     * @return null
     * @covers \Rcm\Factory\PluginManagerFactory
     */
    public function testCreateService()
    {
        $mockEntityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mockModuleManager = $this
            ->getMockBuilder('\Zend\ModuleManager\ModuleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mockViewRenderer = $this
            ->getMockBuilder('\Zend\View\Renderer\RendererInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRequest = $this
            ->getMockBuilder('Zend\Http\PhpEnvironment\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache = $this->getMockBuilder('\Zend\Cache\Storage\Adapter\Memory')
            ->disableOriginalConstructor()
            ->getMock();

        $sm = new ServiceManager();
        $sm->setService('Doctrine\ORM\EntityManager', $mockEntityManager);
        $sm->setService('moduleManager', $mockModuleManager);
        $sm->setService('ViewRenderer', $mockViewRenderer);
        $sm->setService('request', $mockRequest);
        $sm->setService('Rcm\Service\Cache', $mockCache);
        $sm->setService('config', array());

        $factory = new PluginManagerFactory();
        $object = $factory->createService($sm);

        $this->assertTrue($object instanceof PluginManager);
    }
}