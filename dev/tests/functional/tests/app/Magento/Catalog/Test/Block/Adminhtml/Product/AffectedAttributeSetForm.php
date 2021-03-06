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

namespace Magento\Catalog\Test\Block\Adminhtml\Product;

use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Magento\Backend\Test\Block\Widget\Form as ParentForm;

/**
 * Class AffectedAttributeSet
 * Choose affected attribute set dialog popup window
 */
class AffectedAttributeSetForm extends ParentForm
{
    /**
     * 'Confirm' button container locator
     *
     * @var string
     */
    protected $confirmButtonContainer = '//parent::div[div[@id="affected-attribute-set-form"]]';

    /**
     * 'Confirm' button locator
     *
     * @var string
     */
    protected $confirmButton = '//button[contains(@id,"confirm-button")]';

    /**
     * Locator buttons new name attribute set
     *
     * @var string
     */
    protected $affectedAttributeSetNew = '#affected-attribute-set-new';

    /**
     * Fill popup form
     *
     * @param FixtureInterface $product
     * @param Element|null $element
     * @return $this
     */
    public function fill(FixtureInterface $product, Element $element = null)
    {
        $data = $product->getData('affect_configurable_product_attributes');
        if (!empty($data)) {
            $this->_rootElement->find($this->affectedAttributeSetNew)->click();
            $fields = ['new_attribute_set_name' => strval($data)];
            $mapping = $this->dataMapping($fields);
            $this->_fill($mapping, $element);
        }
        return $this;
    }

    /**
     * Click confirm button
     *
     * @return void
     */
    public function confirm()
    {
        $isVisible = $this->_rootElement->find(
            $this->confirmButtonContainer . $this->confirmButton,
            Locator::SELECTOR_XPATH
        )->isVisible();

        if ($isVisible) {
            $this->_rootElement->find(
                $this->confirmButtonContainer . $this->confirmButton,
                Locator::SELECTOR_XPATH
            )->click();
        }
    }
}
