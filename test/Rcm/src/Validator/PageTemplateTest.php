<?php
/**
 * Unit Test for the PageTemplate Validator
 *
 * This file contains the unit test for the PageTemplate Validator
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

namespace RcmTest\Validator;

use Rcm\Validator\PageTemplate;

require_once __DIR__ . '/../../../autoload.php';

/**
 * Unit Test for the PageTemplate Validator
 *
 * Unit Test for the PageTemplate Validator
 *
 * @category  Reliv
 * @package   Rcm
 * @author    Westin Shafer <wshafer@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 * @link      http://github.com/reliv
 */
class PageTemplateTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $pageManager;

    /** @var \Rcm\Validator\PageTemplate */
    protected $validator;

    /**
     * Setup for tests
     *
     * @return void
     */
    public function setup()
    {
        $pageManager = $this->getMockBuilder('\Rcm\Service\PageManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageManager = $pageManager;

        /** @var \Rcm\Service\PageManager $pageManager */
        $this->validator = new PageTemplate($pageManager);
    }

    /**
     * Test Constructor
     *
     * @return void
     *
     * @covers \Rcm\Validator\PageTemplate::__construct
     */
    public function testConstructor()
    {
        $this->assertInstanceOf('Rcm\Validator\PageTemplate', $this->validator);
    }

    /**
     * Test Set Page Type
     *
     * @return void
     *
     * @covers \Rcm\Validator\PageTemplate::setPageType
     */
    public function testSetPageType()
    {
        $reflectedClass = new \ReflectionClass($this->validator);
        $reflectedProp = $reflectedClass->getProperty('pageType');
        $reflectedProp->setAccessible(true);
        $defaultValue = $reflectedProp->getValue($this->validator);

        $this->assertEquals('t', $defaultValue);

        $this->validator->setPageType('z');

        $result = $reflectedProp->getValue($this->validator);

        $this->assertEquals('z', $result);
    }

    /**
     * Test Set Page Type
     *
     * @return void
     *
     * @covers \Rcm\Validator\PageTemplate::setSiteId
     */
    public function testSetSiteId()
    {
        $reflectedClass = new \ReflectionClass($this->validator);
        $reflectedProp = $reflectedClass->getProperty('siteId');
        $reflectedProp->setAccessible(true);
        $defaultValue = $reflectedProp->getValue($this->validator);

        $this->assertEquals(null, $defaultValue);

        $this->validator->setSiteId(22);

        $result = $reflectedProp->getValue($this->validator);

        $this->assertEquals(22, $result);
    }

    /**
     * Test Is Valid
     *
     * @return void
     *
     * @covers \Rcm\Validator\PageTemplate::isValid
     */
    public function testIsValid()
    {
        $templateId = 44;
        $pageType = 'z';

        $this->pageManager->expects($this->once())
            ->method('getPageById')
            ->with(
                $this->equalTo($templateId),
                $this->equalTo($pageType),
                $this->equalTo(null)
            )->will($this->returnValue(true));

        $this->validator->setPageType($pageType);

        $result = $this->validator->isValid($templateId);

        $this->assertTrue($result);

        $this->assertEmpty($this->validator->getMessages());
    }

    /**
     * Test Is Valid when page exists
     *
     * @return void
     *
     * @covers \Rcm\Validator\PageTemplate::isValid
     */
    public function testIsValidWhenPageTemplateIdInvalid()
    {
        $templateId = 44;
        $pageType = 'z';

        $this->pageManager->expects($this->once())
            ->method('getPageById')
            ->with(
                $this->equalTo($templateId),
                $this->equalTo($pageType),
                $this->equalTo(null)
            )->will($this->returnValue(false));

        $this->validator->setPageType($pageType);

        $result = $this->validator->isValid($templateId);

        $this->assertFalse($result);

        $messages = $this->validator->getMessages();

        $this->assertNotEmpty($messages);

        $errors = array_keys($messages);

        $this->assertEquals('pageTemplate', $errors[0]);
    }
}