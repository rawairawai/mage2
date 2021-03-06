<?php
/**
 * Test for \Magento\Integration\Service\V1\Integration
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Service\V1;

use Magento\Integration\Model\Integration;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    const VALUE_INTEGRATION_ID = 1;

    const VALUE_INTEGRATION_NAME = 'Integration Name';

    const VALUE_INTEGRATION_ANOTHER_NAME = 'Another Integration Name';

    const VALUE_INTEGRATION_EMAIL = 'test@magento.com';

    const VALUE_INTEGRATION_SETUP_BACKEND = 0;

    const VALUE_INTEGRATION_ENDPOINT = 'http://magento.ll/endpoint';

    const VALUE_INTEGRATION_CONSUMER_ID = 1;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $_integrationFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $_integrationMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $_emptyIntegrationMock;

    /** @var \Magento\Integration\Service\V1\Integration */
    private $_service;

    /** @var array */
    private $_integrationData;

    protected function setUp()
    {
        $this->_integrationFactory = $this->getMockBuilder(
            'Magento\Integration\Model\Integration\Factory'
        )->disableOriginalConstructor()->getMock();
        $this->_integrationMock = $this->getMockBuilder(
            'Magento\Integration\Model\Integration'
        )->disableOriginalConstructor()->setMethods(
            array(
                'getData',
                'getId',
                'getName',
                'getEmail',
                'getEndpoint',
                'load',
                'loadByName',
                'save',
                'delete',
                '__wakeup'
            )
        )->getMock();
        $this->_integrationData = array(
            Integration::ID => self::VALUE_INTEGRATION_ID,
            Integration::NAME => self::VALUE_INTEGRATION_NAME,
            Integration::EMAIL => self::VALUE_INTEGRATION_EMAIL,
            Integration::EMAIL => self::VALUE_INTEGRATION_ENDPOINT,
            Integration::SETUP_TYPE => self::VALUE_INTEGRATION_SETUP_BACKEND
        );
        $this->_integrationFactory->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_integrationMock)
        );

        $userIdentifierFactory = $this->getMockBuilder(
            'Magento\Authz\Model\UserIdentifier\Factory'
        )->disableOriginalConstructor()->getMock();
        $oauthConsumerHelper = $this->getMockBuilder(
            'Magento\Integration\Service\V1\Oauth'
        )->disableOriginalConstructor()->getMock();
        $oauthConsumer = $this->getMockBuilder(
            'Magento\Integration\Model\Oauth\Consumer'
        )->disableOriginalConstructor()->getMock();
        $oauthConsumerHelper->expects(
            $this->any()
        )->method(
            'createConsumer'
        )->will(
            $this->returnValue($oauthConsumer)
        );
        $oauthConsumerHelper->expects($this->any())->method('loadConsumer')->will($this->returnValue($oauthConsumer));
        $userIdentifier = $this->getMockBuilder(
            'Magento\Authz\Model\UserIdentifier'
        )->disableOriginalConstructor()->getMock();
        $userIdentifierFactory->expects($this->any())->method('create')->will($this->returnValue($userIdentifier));

        $this->_service = new \Magento\Integration\Service\V1\Integration(
            $this->_integrationFactory,
            $oauthConsumerHelper
        );
        $this->_emptyIntegrationMock = $this->getMockBuilder(
            'Magento\Integration\Model\Integration'
        )->disableOriginalConstructor()->setMethods(
            array(
                'getData',
                'getId',
                'getName',
                'getEmail',
                'getEndpoint',
                'load',
                'loadByName',
                'save',
                'delete',
                '__wakeup'
            )
        )->getMock();
        $this->_emptyIntegrationMock->expects($this->any())->method('getId')->will($this->returnValue(null));
    }

    public function testCreateSuccess()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getId'
        )->will(
            $this->returnValue(self::VALUE_INTEGRATION_ID)
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getData'
        )->will(
            $this->returnValue($this->_integrationData)
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_NAME,
            'name'
        )->will(
            $this->returnValue($this->_emptyIntegrationMock)
        );
        $this->_integrationMock->expects($this->any())->method('save')->will($this->returnSelf());
        $this->_setValidIntegrationData();
        $resultData = $this->_service->create($this->_integrationData)->getData();
        $this->assertSame($this->_integrationData, $resultData);
    }

    /**
     * @expectedException \Magento\Integration\Exception
     * @expectedExceptionMessage Integration with name 'Integration Name' exists.
     */
    public function testCreateIntegrationAlreadyExistsException()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getId'
        )->will(
            $this->returnValue(self::VALUE_INTEGRATION_ID)
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getData'
        )->will(
            $this->returnValue($this->_integrationData)
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_NAME,
            'name'
        )->will(
            $this->returnValue($this->_integrationMock)
        );
        $this->_integrationMock->expects($this->never())->method('save')->will($this->returnSelf());
        $this->_service->create($this->_integrationData);
    }

    public function testUpdateSuccess()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getId'
        )->will(
            $this->returnValue(self::VALUE_INTEGRATION_ID)
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getData'
        )->will(
            $this->returnValue($this->_integrationData)
        );
        $this->_integrationMock->expects(
            $this->at(0)
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_ID
        )->will(
            $this->returnValue($this->_integrationMock)
        );
        $this->_integrationMock->expects($this->once())->method('save')->will($this->returnSelf());
        $this->_setValidIntegrationData();
        $integrationData = $this->_service->update($this->_integrationData)->getData();
        $this->assertEquals($this->_integrationData, $integrationData);
    }

    public function testUpdateSuccessNameChanged()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getId'
        )->will(
            $this->returnValue(self::VALUE_INTEGRATION_ID)
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'load'
        )->will(
            $this->onConsecutiveCalls($this->_integrationMock, $this->_emptyIntegrationMock)
        );
        $this->_integrationMock->expects($this->once())->method('save')->will($this->returnSelf());
        $this->_setValidIntegrationData();
        $integrationData = array(
            'integration_id' => self::VALUE_INTEGRATION_ID,
            'name' => self::VALUE_INTEGRATION_ANOTHER_NAME,
            'email' => self::VALUE_INTEGRATION_EMAIL,
            'endpoint' => self::VALUE_INTEGRATION_ENDPOINT
        );
        $this->_integrationMock->expects($this->any())->method('getData')->will($this->returnValue($integrationData));

        $updatedData = $this->_service->update($integrationData)->getData();
        $this->assertEquals($integrationData, $updatedData);
    }

    /**
     * @expectedException \Magento\Integration\Exception
     * @expectedExceptionMessage Integration with name 'Another Integration Name' exists.
     */
    public function testUpdateException()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getId'
        )->will(
            $this->returnValue(self::VALUE_INTEGRATION_ID)
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'load'
        )->will(
            $this->onConsecutiveCalls($this->_integrationMock, $this->_getAnotherIntegrationMock())
        );
        $this->_integrationMock->expects($this->never())->method('save')->will($this->returnSelf());
        $this->_setValidIntegrationData();
        $integrationData = array(
            'integration_id' => self::VALUE_INTEGRATION_ID,
            'name' => self::VALUE_INTEGRATION_ANOTHER_NAME,
            'email' => self::VALUE_INTEGRATION_EMAIL,
            'endpoint' => self::VALUE_INTEGRATION_ENDPOINT
        );
        $this->_service->update($integrationData);
    }

    public function testGet()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getId'
        )->will(
            $this->returnValue(self::VALUE_INTEGRATION_ID)
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getData'
        )->will(
            $this->returnValue($this->_integrationData)
        );
        $this->_integrationMock->expects($this->once())->method('load')->will($this->returnSelf());
        $this->_integrationMock->expects($this->never())->method('save');
        $integrationData = $this->_service->get(self::VALUE_INTEGRATION_ID)->getData();
        $this->assertEquals($this->_integrationData, $integrationData);
    }

    /**
     * @expectedException \Magento\Integration\Exception
     * @expectedExceptionMessage Integration with ID '1' does not exist.
     */
    public function testGetException()
    {
        $this->_integrationMock->expects($this->any())->method('getId')->will($this->returnValue(null));
        $this->_integrationMock->expects($this->once())->method('load')->will($this->returnSelf());
        $this->_integrationMock->expects($this->never())->method('save');
        $this->_service->get(self::VALUE_INTEGRATION_ID)->getData();
    }

    public function testFindByName()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_NAME,
            'name'
        )->will(
            $this->returnValue($this->_integrationMock)
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getData'
        )->will(
            $this->returnValue($this->_integrationData)
        );
        $integration = $this->_service->findByName(self::VALUE_INTEGRATION_NAME);
        $this->assertEquals($this->_integrationData[Integration::NAME], $integration->getData()[Integration::NAME]);
    }

    public function testFindByNameNotFound()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_NAME,
            'name'
        )->will(
            $this->returnValue($this->_emptyIntegrationMock)
        );
        $this->_emptyIntegrationMock->expects($this->any())->method('getData')->will($this->returnValue(null));
        $integration = $this->_service->findByName(self::VALUE_INTEGRATION_NAME);
        $this->assertNull($integration->getData());
    }

    public function testDelete()
    {
        $this->_integrationMock->expects(
            $this->once()
        )->method(
            'getId'
        )->will(
            $this->returnValue(self::VALUE_INTEGRATION_ID)
        );
        $this->_integrationMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_ID
        )->will(
            $this->returnValue($this->_integrationMock)
        );
        $this->_integrationMock->expects(
            $this->once()
        )->method(
            'delete'
        )->will(
            $this->returnValue($this->_integrationMock)
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getData'
        )->will(
            $this->returnValue($this->_integrationData)
        );
        $integrationData = $this->_service->delete(self::VALUE_INTEGRATION_ID);
        $this->assertEquals($this->_integrationData[Integration::ID], $integrationData[Integration::ID]);
    }

    /**
     * @expectedException \Magento\Integration\Exception
     * @expectedExceptionMessage Integration with ID '1' does not exist.
     */
    public function testDeleteException()
    {
        $this->_integrationMock->expects($this->any())->method('getId')->will($this->returnValue(null));
        $this->_integrationMock->expects($this->once())->method('load')->will($this->returnSelf());
        $this->_integrationMock->expects($this->never())->method('delete');
        $this->_service->delete(self::VALUE_INTEGRATION_ID);
    }

    public function testFindByConsumerId()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getData'
        )->will(
            $this->returnValue($this->_integrationData)
        );

        $this->_integrationMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_CONSUMER_ID,
            'consumer_id'
        )->will(
            $this->returnValue($this->_integrationMock)
        );

        $integration = $this->_service->findByConsumerId(self::VALUE_INTEGRATION_CONSUMER_ID);
        $this->assertEquals($this->_integrationData[Integration::NAME], $integration->getData()[Integration::NAME]);
    }

    public function testFindByConsumerIdNotFound()
    {
        $this->_emptyIntegrationMock->expects($this->any())->method('getData')->will($this->returnValue(null));

        $this->_integrationMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_CONSUMER_ID,
            'consumer_id'
        )->will(
            $this->returnValue($this->_emptyIntegrationMock)
        );

        $integration = $this->_service->findByConsumerId(1);
        $this->assertNull($integration->getData());
    }

    /**
     * Set valid integration data
     */
    private function _setValidIntegrationData()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getName'
        )->will(
            $this->returnValue(self::VALUE_INTEGRATION_NAME)
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getEmail'
        )->will(
            $this->returnValue(self::VALUE_INTEGRATION_EMAIL)
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getEndpoint'
        )->will(
            $this->returnValue(self::VALUE_INTEGRATION_ENDPOINT)
        );
    }

    /**
     * Create mock integration
     *
     * @param string $name
     * @param int $integrationId
     * @return mixed
     */
    private function _getAnotherIntegrationMock(
        $name = self::VALUE_INTEGRATION_NAME,
        $integrationId = self::VALUE_INTEGRATION_ID
    ) {
        $integrationMock = $this->getMockBuilder(
            'Magento\Integration\Model\Integration'
        )->disableOriginalConstructor()->setMethods(
            array(
                'getData',
                'getId',
                'getName',
                'getEmail',
                'getEndpoint',
                'load',
                'loadByName',
                'save',
                'delete',
                '__wakeup'
            )
        )->getMock();
        $integrationMock->expects($this->any())->method('getId')->will($this->returnValue($integrationId));
        $integrationMock->expects($this->any())->method('getName')->will($this->returnValue($name));
        $integrationMock->expects(
            $this->any()
        )->method(
            'getEmail'
        )->will(
            $this->returnValue(self::VALUE_INTEGRATION_EMAIL)
        );
        $integrationMock->expects(
            $this->any()
        )->method(
            'getEndpoint'
        )->will(
            $this->returnValue(self::VALUE_INTEGRATION_ENDPOINT)
        );
        return $integrationMock;
    }
}
