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
namespace Magento\Sales\Model\Resource\Order;

/**
 * Flat sales order shipment resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Shipment extends AbstractOrder
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_shipment_resource';

    /**
     * Is grid available
     *
     * @var bool
     */
    protected $_grid = true;

    /**
     * Use increment id
     *
     * @var bool
     */
    protected $_useIncrementId = true;

    /**
     * Entity type for increment id
     *
     * @var string
     */
    protected $_entityTypeForIncrementId = 'shipment';

    /**
     * Fields that should be serialized before persistence
     *
     * @var array
     */
    protected $_serializableFields = array('packages' => array(array(), array()));

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_flat_shipment', 'entity_id');
    }

    /**
     * Init virtual grid records for entity
     *
     * @return $this
     */
    protected function _initVirtualGridColumns()
    {
        parent::_initVirtualGridColumns();
        $adapter = $this->getReadConnection();
        $checkedFirstname = $adapter->getIfNullSql('{{table}}.firstname', $adapter->quote(''));
        $checkedLastname = $adapter->getIfNullSql('{{table}}.lastname', $adapter->quote(''));
        $concatName = $adapter->getConcatSql(array($checkedFirstname, $adapter->quote(' '), $checkedLastname));

        $this->addVirtualGridColumn(
            'shipping_name',
            'sales_flat_order_address',
            array('shipping_address_id' => 'entity_id'),
            $concatName
        )->addVirtualGridColumn(
            'order_increment_id',
            'sales_flat_order',
            array('order_id' => 'entity_id'),
            'increment_id'
        )->addVirtualGridColumn(
            'order_created_at',
            'sales_flat_order',
            array('order_id' => 'entity_id'),
            'created_at'
        );

        return $this;
    }
}
