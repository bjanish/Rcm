<?php
/**
 * Unit Test for the Plugin Wrapper
 *
 * This file contains the unit test for the Plugin Wrapper
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

namespace RcmTest\Entity;

require_once __DIR__ . '/../../../autoload.php';

use Rcm\Entity\PluginInstance;
use Rcm\Entity\PluginWrapper;

/**
 * Unit Test for the Plugin Wrapper
 *
 * Unit Test for the Plugin Wrapper
 *
 * @category  Reliv
 * @package   Rcm
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 * @link      http://github.com/reliv
 */
class PluginWrapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Rcm\Entity\PluginWrapper */
    protected $pluginWrapper;

    /**
     * Setup for tests
     *
     * @return void
     */
    public function setup()
    {
        $this->pluginWrapper = new PluginWrapper();
    }

    /**
     * Test Get and Set Plugin Wrapper Id
     *
     * @return void
     *
     * @covers \Rcm\Entity\PluginWrapper
     */
    public function testGetAndSetPluginWrapperId()
    {
        $id = 4;

        $this->pluginWrapper->setPluginWrapperId($id);

        $actual = $this->pluginWrapper->getPluginWrapperId();

        $this->assertEquals($id, $actual);
    }

    /**
     * Test Get and Set Layout Container
     *
     * @return void
     *
     * @covers \Rcm\Entity\PluginWrapper
     */
    public function testGetAndSetLayoutContainer()
    {
        $container = 4;

        $this->pluginWrapper->setLayoutContainer($container);

        $actual = $this->pluginWrapper->getLayoutContainer();

        $this->assertEquals($container, $actual);
    }

    /**
     * Test Get and Set Render Order Number
     *
     * @return void
     *
     * @covers \Rcm\Entity\PluginWrapper
     */
    public function testGetAndSetRenderOrderNumber()
    {
        $order = 4;

        $this->pluginWrapper->setRenderOrderNumber($order);

        $actual = $this->pluginWrapper->getRenderOrderNumber();

        $this->assertEquals($order, $actual);
    }

    /**
     * Test Get and Set Plugin Instance
     *
     * @return void
     *
     * @covers \Rcm\Entity\PluginWrapper
     */
    public function testGetAndSetInstance()
    {
        $plugin = new PluginInstance();
        $plugin->setInstanceId(44);

        $this->pluginWrapper->setInstance($plugin);

        $actual = $this->pluginWrapper->getInstance();

        $this->assertTrue($plugin instanceof PluginInstance);
        $this->assertEquals($plugin, $actual);
    }

    /**
     * Test Set Plugin Instance Only Accepts a Plugin Instance object
     *
     * @return void
     *
     * @covers \Rcm\Entity\PluginWrapper
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetCreatedDateOnlyAcceptsDateTime()
    {
        $this->pluginWrapper->setInstance(time());
    }

    /**
     * Test Get and Set Height
     *
     * @return void
     *
     * @covers \Rcm\Entity\PluginWrapper
     */
    public function testGetAndSetHeight()
    {
        $height = '140';

        $this->pluginWrapper->setHeight($height);

        $actual = $this->pluginWrapper->getHeight();

        $this->assertEquals($height . 'px', $actual);
    }

    /**
     * Test Get and Set Width
     *
     * @return void
     *
     * @covers \Rcm\Entity\PluginWrapper
     */
    public function testGetAndSetWidth()
    {
        $width = '140';

        $this->pluginWrapper->setWidth($width);

        $actual = $this->pluginWrapper->getWidth();

        $this->assertEquals($width . 'px', $actual);
    }

    /**
     * Test Get and Set Div Float
     *
     * @return void
     *
     * @covers \Rcm\Entity\PluginWrapper
     */
    public function testGetAndSetDivFloat()
    {
        $float = 'right';

        $this->pluginWrapper->setDivFloat($float);

        $actual = $this->pluginWrapper->getDivFloat();

        $this->assertEquals($float, $actual);
    }

    /**
     * Test Clone
     *
     * @return void
     *
     * @covers \Rcm\Entity\PluginWrapper
     */
    public function testClone()
    {
        $instances = array(
            0 => array(
                'pluginWrapperId' => 43,
                'layoutContainer' => 'layoutOne',
                'renderOrder' => 0,
                'height' => 32,
                'width' => 100,
                'divFloat' => 'right',
                'instance' => array(
                    'pluginInstanceId' => 44,
                    'plugin' => 'MockPlugin',
                    'siteWide' => false,
                    'displayName' => null,
                    'instanceConfig' => array(
                        'var1' => 1,
                        'var2' => 2
                    ),
                    'md5' => 'firstMd5'
                ),
            ),

            1 => array(
                'pluginWrapperId' => 45,
                'layoutContainer' => 'layoutTwo',
                'renderOrder' => 1,
                'height' => 33,
                'width' => 101,
                'divFloat' => 'none',
                'instance' => array(
                    'pluginInstanceId' => 46,
                    'plugin' => 'MockPlugin2',
                    'siteWide' => true,
                    'displayName' => 'TestSiteWide',
                    'instanceConfig' => array(
                        'var3' => 3,
                        'var4' => 4
                    ),
                    'md5' => 'secondMd5'
                ),
            ),
        );

        foreach ($instances as $instance) {
            $plugin = new PluginInstance();
            $plugin->setInstanceId($instance['instance']['pluginInstanceId']);
            $plugin->setPlugin($instance['instance']['plugin']);

            if ($instance['instance']['siteWide']) {
                $plugin->setSiteWide();
            }

            $plugin->setDisplayName($instance['instance']['displayName']);
            $plugin->setInstanceConfig($instance['instance']['instanceConfig']);
            $plugin->setMd5($instance['instance']['md5']);

            $wrapper = new PluginWrapper();
            $wrapper->setPluginWrapperId($instance['pluginWrapperId']);
            $wrapper->setLayoutContainer($instance['layoutContainer']);
            $wrapper->setRenderOrderNumber($instance['renderOrder']);
            $wrapper->setHeight($instance['height']);
            $wrapper->setWidth($instance['width']);
            $wrapper->setDivFloat($instance['divFloat']);
            $wrapper->setInstance($plugin);

            $clonedWrapper = clone $wrapper;

            $this->assertNotEquals(
                $wrapper->getPluginWrapperId(),
                $clonedWrapper->getPluginWrapperId()
            );

            $this->assertNull($clonedWrapper->getPluginWrapperId());

            $this->assertEquals(
                $wrapper->getLayoutContainer(),
                $clonedWrapper->getLayoutContainer()
            );

            $this->assertEquals(
                $wrapper->getRenderOrderNumber(),
                $clonedWrapper->getRenderOrderNumber()
            );

            $this->assertEquals(
                $wrapper->getHeight(),
                $clonedWrapper->getHeight()
            );

            $this->assertEquals(
                $wrapper->getWidth(),
                $clonedWrapper->getWidth()
            );

            $this->assertEquals(
                $wrapper->getDivFloat(),
                $clonedWrapper->getDivFloat()
            );

            $preInstance = $wrapper->getInstance();
            $clonedInstance = $clonedWrapper->getInstance();

            if (!$instance['instance']['siteWide']) {
                $this->assertNotEquals(
                    $preInstance->getInstanceId(),
                    $clonedInstance->getInstanceId()
                );

                $this->assertNull($clonedInstance->getInstanceId());
            } else {
                $this->assertEquals(
                    $preInstance->getInstanceId(),
                    $clonedInstance->getInstanceId()
                );
            }

            $this->assertEquals(
                $preInstance->getPlugin(),
                $clonedInstance->getPlugin()
            );

            $this->assertEquals(
                $preInstance->isSiteWide(),
                $clonedInstance->isSiteWide()
            );

            $this->assertEquals(
                $preInstance->getDisplayName(),
                $clonedInstance->getDisplayName()
            );

            $this->assertEquals(
                $preInstance->getInstanceConfig(),
                $clonedInstance->getInstanceConfig()
            );

            $this->assertEquals(
                $preInstance->getMd5(),
                $clonedInstance->getMd5()
            );
        }
    }
}
 