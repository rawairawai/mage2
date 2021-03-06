<?php
/**
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

namespace Magento\Weee\Pricing\Render;

/**
 * Class AdjustmentTest for testing Adjustment class
 *
 */
class AdjustmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Weee\Pricing\Render\Adjustment
     */
    protected $model;

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelperMock;

    /**
     * Context mock
     *
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $contextMock;

    /**
     * Price currency model mock
     *
     * @var \Magento\Directory\Model\PriceCurrency
     */
    protected $priceCurrencyMock;

    /**
     * Set up mocks for tested class
     */
    public function setUp()
    {
        $this->contextMock = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->priceCurrencyMock = $this->getMockForAbstractClass(
            'Magento\Framework\Pricing\PriceCurrencyInterface',
            [],
            '',
            true,
            true,
            true,
            []
        );
        $this->weeeHelperMock = $this->getMock('\Magento\Weee\Helper\Data', [], [], '', false);
        $eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $storeConfigMock = $this->getMockBuilder('Magento\Store\Model\Store\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfigMock = $this->getMockForAbstractClass('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->contextMock->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManagerMock));
        $this->contextMock->expects($this->any())
            ->method('getStoreConfig')
            ->will($this->returnValue($storeConfigMock));
        $this->contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->will($this->returnValue($scopeConfigMock));

        $this->model = new Adjustment(
            $this->contextMock,
            $this->priceCurrencyMock,
            $this->weeeHelperMock
        );
    }

    /**
     * Test for method getAdjustmentCode
     */
    public function testGetAdjustmentCode()
    {
        $this->assertEquals(\Magento\Weee\Pricing\Adjustment::ADJUSTMENT_CODE, $this->model->getAdjustmentCode());
    }

    /**
     * Test for method getFinalAmount
     */
    public function testGetFinalAmount()
    {
        $expectedValue = 10;
        $typeOfDisplay = 1; //Just to set it to not false
        /** @var \Magento\Framework\Pricing\Render\Amount $amountRender */
        $amountRender = $this->getMockBuilder('Magento\Framework\Pricing\Render\Amount')
            ->disableOriginalConstructor()
            ->setMethods(['getSaleableItem', 'getDisplayValue', 'getAmount'])
            ->getMock();
        $amountRender->expects($this->any())
            ->method('getDisplayValue')
            ->will($this->returnValue($expectedValue));
        $this->weeeHelperMock->expects($this->any())->method('typeOfDisplay')->will($this->returnValue($typeOfDisplay));
        /** @var \Magento\Framework\Pricing\Amount\Base $baseAmount */
        $baseAmount = $this->getMockBuilder('Magento\Framework\Pricing\Amount\Base')
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $amountRender->expects($this->any())
            ->method('getAmount')
            ->will($this->returnValue($baseAmount));

        $this->model->render($amountRender);
        $result = $this->model->getFinalAmount();

        $this->assertEquals($expectedValue, $result);
    }

    /**
     * Test for method showInclDescr
     *
     * @dataProvider showInclDescrDataProvider
     */
    public function testShowInclDescr($typeOfDisplay, $amount, $expectedResult)
    {
        /** @var \Magento\Framework\Pricing\Render\Amount $amountRender */
        $amountRender = $this->getMockBuilder('Magento\Framework\Pricing\Render\Amount')
            ->disableOriginalConstructor()
            ->setMethods(['getSaleableItem', 'getDisplayValue', 'getAmount'])
            ->getMock();
        /** @var \Magento\Catalog\Model\Product $saleable */
        $saleable = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();
        /** @var \Magento\Framework\Pricing\Amount\Base $baseAmount */
        $baseAmount = $this->getMockBuilder('Magento\Framework\Pricing\Amount\Base')
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();

        $baseAmount->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($amount));

        $amountRender->expects($this->any())
            ->method('getAmount')
            ->will($this->returnValue($baseAmount));

        $callback = function ($argument) use ($typeOfDisplay) {
            if (is_array($argument)) {
                return in_array($typeOfDisplay, $argument);
            } else {
                return $argument == $typeOfDisplay;
            }
        };

        $this->weeeHelperMock->expects($this->any())->method('typeOfDisplay')->will($this->returnCallback($callback));
        $this->weeeHelperMock->expects($this->any())->method('getAmount')->will($this->returnValue($amount));
        $amountRender->expects($this->any())->method('getSaleableItem')->will($this->returnValue($saleable));

        $this->model->render($amountRender);
        $result = $this->model->showInclDescr();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testShowInclDescr
     *
     * @return array
     */
    public function showInclDescrDataProvider()
    {
        return [
            [\Magento\Weee\Model\Tax::DISPLAY_INCL, 1.23, false],
            [\Magento\Weee\Model\Tax::DISPLAY_INCL_DESCR, 1.23, true],
            [\Magento\Weee\Model\Tax::DISPLAY_EXCL_DESCR_INCL, 1.23, false],
            [\Magento\Weee\Model\Tax::DISPLAY_EXCL, 1.23, false],
            [4, 1.23, false],
            [\Magento\Weee\Model\Tax::DISPLAY_INCL, 0, false],
            [\Magento\Weee\Model\Tax::DISPLAY_INCL_DESCR, 0, false],
            [\Magento\Weee\Model\Tax::DISPLAY_EXCL_DESCR_INCL, 0, false],
            [\Magento\Weee\Model\Tax::DISPLAY_EXCL, 0, false],
            [4, 0, false],
        ];
    }

    /**
     * Test method for showExclDescrIncl
     *
     * @param int $typeOfDisplay
     * @param float $amount
     * @param bool $expectedResult
     * @dataProvider showExclDescrInclDataProvider
     */
    public function testShowExclDescrIncl($typeOfDisplay, $amount, $expectedResult)
    {
        /** @var \Magento\Framework\Pricing\Render\Amount $amountRender */
        $amountRender = $this->getMockBuilder('Magento\Framework\Pricing\Render\Amount')
            ->disableOriginalConstructor()
            ->setMethods(['getSaleableItem', 'getDisplayValue', 'getAmount'])
            ->getMock();
        /** @var \Magento\Catalog\Model\Product $saleable */
        $saleable = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();
        /** @var \Magento\Framework\Pricing\Amount\Base $baseAmount */
        $baseAmount = $this->getMockBuilder('Magento\Framework\Pricing\Amount\Base')
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $baseAmount->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($amount));
        $amountRender->expects($this->any())
            ->method('getAmount')
            ->will($this->returnValue($baseAmount));

        $callback = function ($argument) use ($typeOfDisplay) {
            if (is_array($argument)) {
                return in_array($typeOfDisplay, $argument);
            } else {
                return $argument == $typeOfDisplay;
            }
        };

        $this->weeeHelperMock->expects($this->any())->method('typeOfDisplay')->will($this->returnCallback($callback));
        $this->weeeHelperMock->expects($this->any())->method('getAmount')->will($this->returnValue($amount));
        $amountRender->expects($this->any())->method('getSaleableItem')->will($this->returnValue($saleable));

        $this->model->render($amountRender);
        $result = $this->model->showExclDescrIncl();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testShowExclDescrIncl
     *
     * @return array
     */
    public function showExclDescrInclDataProvider()
    {
        return [
            [\Magento\Weee\Model\Tax::DISPLAY_INCL, 1.23, false],
            [\Magento\Weee\Model\Tax::DISPLAY_INCL_DESCR, 1.23, false],
            [\Magento\Weee\Model\Tax::DISPLAY_EXCL_DESCR_INCL, 1.23, true],
            [\Magento\Weee\Model\Tax::DISPLAY_EXCL, 1.23, false],
            [4, 1.23, false],
            [\Magento\Weee\Model\Tax::DISPLAY_INCL, 0, false],
            [\Magento\Weee\Model\Tax::DISPLAY_INCL_DESCR, 0, false],
            [\Magento\Weee\Model\Tax::DISPLAY_EXCL_DESCR_INCL, 0, false],
            [\Magento\Weee\Model\Tax::DISPLAY_EXCL, 0, false],
            [4, 0, false],
        ];
    }

    /**
     * Test for method getWeeeTaxAttributes
     *
     * @param int $typeOfDisplay
     * @param array $attributes
     * @param array $expectedResult
     * @dataProvider getWeeeTaxAttributesDataProvider
     */
    public function testGetWeeeTaxAttributes($typeOfDisplay, $attributes, $expectedResult)
    {
        /** @var \Magento\Framework\Pricing\Render\Amount $amountRender */
        $amountRender = $this->getMockBuilder('Magento\Framework\Pricing\Render\Amount')
            ->disableOriginalConstructor()
            ->setMethods(['getSaleableItem', 'getDisplayValue', 'getAmount'])
            ->getMock();
        /** @var \Magento\Catalog\Model\Product $saleable */
        $saleable = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();
        /** @var \Magento\Framework\Pricing\Amount\Base $baseAmount */
        $baseAmount = $this->getMockBuilder('Magento\Framework\Pricing\Amount\Base')
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $amountRender->expects($this->any())
            ->method('getAmount')
            ->will($this->returnValue($baseAmount));
        $callback = function ($argument) use ($typeOfDisplay) {
            if (is_array($argument)) {
                return in_array($typeOfDisplay, $argument);
            } else {
                return $argument == $typeOfDisplay;
            }
        };
        $this->weeeHelperMock->expects($this->any())->method('typeOfDisplay')->will($this->returnCallback($callback));
        $this->weeeHelperMock->expects($this->any())
            ->method('getProductWeeeAttributesForDisplay')
            ->will($this->returnValue($attributes));
        $amountRender->expects($this->any())->method('getSaleableItem')->will($this->returnValue($saleable));

        $this->model->render($amountRender);
        $result = $this->model->getWeeeTaxAttributes();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testGetWeeeTaxAttributes
     *
     * @return array
     */
    public function getWeeeTaxAttributesDataProvider()
    {
        return [
            [\Magento\Weee\Model\Tax::DISPLAY_INCL, [1,2,3], []],
            [\Magento\Weee\Model\Tax::DISPLAY_INCL_DESCR, [1,2,3], [1,2,3]],
            [\Magento\Weee\Model\Tax::DISPLAY_EXCL_DESCR_INCL, [1,2,3], [1,2,3]],
            [\Magento\Weee\Model\Tax::DISPLAY_EXCL, [1,2,3], []],
            [4, [1,2,3], []],
        ];
    }

    /**
     * Test for method renderWeeeTaxAttribute
     *
     * @param \Magento\Framework\Object $attribute
     * @param string $expectedResult
     * @dataProvider renderWeeeTaxAttributeDataProvider
     */
    public function testRenderWeeeTaxAttribute($attribute, $expectedResult)
    {
        $this->priceCurrencyMock->expects($this->any())->method('convertAndFormat')->will($this->returnArgument(0));

        $result = $this->model->renderWeeeTaxAttribute($attribute);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testRenderWeeeTaxAttribute
     *
     * @return array
     */
    public function renderWeeeTaxAttributeDataProvider()
    {
        return [
            [new \Magento\Framework\Object(['name' => 'name1', 'amount' => 51]), 'name1: 51'],
            [new \Magento\Framework\Object(['name' => 'name1', 'amount' => false]), 'name1: '],
            [new \Magento\Framework\Object(['name' => false, 'amount' => 51]), ': 51'],
            [new \Magento\Framework\Object(['name' => false, 'amount' => false]), ': '],
        ];
    }
}
