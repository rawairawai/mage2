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

namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Pricing\Price\FinalPrice;

/**
 * Class DiscountCalculatorTest
 */
class DiscountCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Pricing\Price\DiscountCalculator
     */
    protected $calculator;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\FinalPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $finalPriceMock;

    /**
     * @var \Magento\Bundle\Pricing\Price\DiscountProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceMock;

    /**
     * Test setUp
     */
    public function setUp()
    {
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );
        $this->priceInfoMock = $this->getMock(
            'Magento\Framework\Pricing\PriceInfo\Base',
            ['getPrice', 'getPrices'],
            [],
            '',
            false
        );
        $this->finalPriceMock = $this->getMock(
            'Magento\Catalog\Pricing\Price\FinalPrice',
            [],
            [],
            '',
            false
        );
        $this->priceMock = $this->getMockForAbstractClass('Magento\Bundle\Pricing\Price\DiscountProviderInterface');
        $this->calculator = new \Magento\Bundle\Pricing\Price\DiscountCalculator();
    }

    /**
     * Returns price mock with specified %
     *
     * @param int $value
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPriceMock($value)
    {
        $price = clone $this->priceMock;
        $price->expects($this->exactly(3))
            ->method('getDiscountPercent')
            ->will($this->returnValue($value));
        return $price;
    }

    /**
     * test method calculateDiscount with default price amount
     */
    public function testCalculateDiscountWithDefaultAmount()
    {
        $this->productMock->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo(FinalPrice::PRICE_CODE))
            ->will($this->returnValue($this->finalPriceMock));
        $this->finalPriceMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(100));
        $this->priceInfoMock->expects($this->once())
            ->method('getPrices')
            ->will($this->returnValue(
                [
                    $this->getPriceMock(30),
                    $this->getPriceMock(20),
                    $this->getPriceMock(40),
                ]
            )
        );
        $this->assertEquals(20, $this->calculator->calculateDiscount($this->productMock));
    }

    /**
     * test method calculateDiscount with custom price amount
     */
    public function testCalculateDiscountWithCustomAmount()
    {
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->priceInfoMock->expects($this->once())
            ->method('getPrices')
            ->will($this->returnValue(
                    [
                        $this->getPriceMock(30),
                        $this->getPriceMock(20),
                        $this->getPriceMock(40),
                    ]
                )
            );
        $this->assertEquals(10, $this->calculator->calculateDiscount($this->productMock, 50));
    }
}
