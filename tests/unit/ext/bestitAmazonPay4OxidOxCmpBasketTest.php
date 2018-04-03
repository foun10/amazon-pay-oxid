<?php

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Class bestitAmazonPay4OxidOxCmpBasketTest
 * @coversDefaultClass bestitAmazonPay4Oxid_oxcmp_basket
 */
class bestitAmazonPay4OxidOxCmpBasketTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonPay4Oxid_oxcmp_basket
     * @throws ReflectionException
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $oBestitAmazonPay4OxidOxCmpBasket = new bestitAmazonPay4Oxid_oxcmp_basket();
        self::setValue($oBestitAmazonPay4OxidOxCmpBasket, '_oContainer', $oContainer);

        return $oBestitAmazonPay4OxidOxCmpBasket;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidOxCmpBasket = new bestitAmazonPay4Oxid_oxcmp_basket();
        self::assertInstanceOf('bestitAmazonPay4Oxid_oxcmp_basket', $oBestitAmazonPay4OxidOxCmpBasket);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     * @throws ReflectionException
     */
    public function testGetContainer()
    {
        $oBestitAmazonPay4OxidOxCmpBasket = new bestitAmazonPay4Oxid_oxcmp_basket();
        self::assertInstanceOf(
            'bestitAmazonPay4OxidContainer',
            self::callMethod($oBestitAmazonPay4OxidOxCmpBasket, '_getContainer')
        );
    }

    /**
     * @group unit
     * @covers ::render()
     * @covers ::cleanAmazonPay()
     * @throws oxSystemComponentException
     * @throws ReflectionException
     * @throws oxConnectionException
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    public function testRender()
    {
        $oContainer = $this->_getContainerMock();

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(9))
            ->method('getRequestParameter')
            ->withConsecutive(
                array('cl'),
                array('cl'),
                array('cl'),
                array('cl'),
                array('bestitAmazonPay4OxidErrorCode'),
                array('error'),
                array('cl'),
                array('bestitAmazonPay4OxidErrorCode'),
                array('error')
            )
            ->will($this->onConsecutiveCalls(
                'order',
                'thankyou',
                'some',
                'some',
                '',
                '',
                'some',
                '',
                'errorValue'
            ));
        $oConfig->expects($this->exactly(2))
            ->method('getShopSecureHomeUrl')
            ->will($this->returnValue('shopSecureHomeUrl?'));

        $oContainer->expects($this->exactly(7))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        // Session
        $oSession = $this->_getSessionMock();
        $oSession->expects($this->exactly(5))
            ->method('getVariable')
            ->withConsecutive(
                array('blAmazonSyncChangePayment'),
                array('blAmazonSyncChangePayment'),
                array('amazonOrderReferenceId'),
                array('blAmazonSyncChangePayment'),
                array('amazonOrderReferenceId')
            )
            ->will($this->onConsecutiveCalls(
                false,
                true,
                'amazonOrderReferenceIdValue',
                true,
                'amazonOrderReferenceIdValue'
            ));

        $oContainer->expects($this->exactly(5))
            ->method('getSession')
            ->will($this->returnValue($oSession));

        // Module
        $oModule = $this->_getModuleMock();
        $oModule->expects($this->exactly(2))
            ->method('cleanAmazonPay');

        $oContainer->expects($this->exactly(2))
            ->method('getModule')
            ->will($this->returnValue($oModule));

        // ObjectFactory
        $oUserException = $this->getMock('oxUserException');
        $oUserException->expects($this->exactly(2))
            ->method('setMessage')
            ->withConsecutive(
                array(bestitAmazonPay4Oxid_oxcmp_basket::BESTITAMAZONPAY_ERROR_AMAZON_TERMINATED),
                array('errorValue')
            );

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->exactly(2))
            ->method('createOxidObject')
            ->with('oxUserException')
            ->will($this->returnValue($oUserException));

        $oContainer->expects($this->exactly(2))
            ->method('getObjectFactory')
            ->will($this->returnValue($oObjectFactory));

        // UtilsView
        $oUtilsView = $this->_getUtilsViewMock();
        $oUtilsView->expects($this->exactly(2))
            ->method('addErrorToDisplay')
            ->withConsecutive(
                array($oUserException, false, true)
            );

        $oContainer->expects($this->exactly(2))
            ->method('getUtilsView')
            ->will($this->returnValue($oUtilsView));

        // Utils
        $oUtils = $this->_getUtilsMock();
        $oUtils->expects($this->exactly(2))
            ->method('redirect')
            ->withConsecutive(
                array('shopSecureHomeUrl?cl=basket', false),
                array('shopSecureHomeUrl?cl=basket', false)
            );

        $oContainer->expects($this->exactly(2))
            ->method('getUtils')
            ->will($this->returnValue($oUtils));

        // Client
        $oClient = $this->_getClientMock();
        $oClient->expects($this->exactly(2))
            ->method('cancelOrderReference')
            ->with(null, array('amazon_order_reference_id' => 'amazonOrderReferenceIdValue'));

        $oContainer->expects($this->exactly(2))
            ->method('getClient')
            ->will($this->returnValue($oClient));

        $oBestitAmazonPay4OxidOxCmpBasket = $this->_getObject($oContainer);
        $oBestitAmazonPay4OxidOxCmpBasket->render();
        $oBestitAmazonPay4OxidOxCmpBasket->render();
        $oBestitAmazonPay4OxidOxCmpBasket->render();
        $oBestitAmazonPay4OxidOxCmpBasket->render();
        $oBestitAmazonPay4OxidOxCmpBasket->render();
    }
}
