<?php
/**
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
namespace Magento\Shipping\Controller\Adminhtml\Order;

use Magento\Framework\App\RequestInterface;

class ShipmentLoader
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Model\Service\OrderFactory
     */
    protected $orderServiceFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $trackFactory;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Service\OrderFactory $orderServiceFactory
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Service\OrderFactory $orderServiceFactory,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
    ) {
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->shipmentFactory = $shipmentFactory;
        $this->orderFactory = $orderFactory;
        $this->orderServiceFactory = $orderServiceFactory;
        $this->trackFactory = $trackFactory;
    }

    /**
     * Initialize shipment items QTY
     *
     * @param RequestInterface $request
     * @return array
     */
    protected function _getItemQtys(RequestInterface $request)
    {
        $data = $request->getParam('shipment');
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }

    /**
     * Initialize shipment model instance
     *
     * @param RequestInterface $request
     * @return bool|\Magento\Sales\Model\Order\Shipment
     * @throws \Magento\Framework\Model\Exception
     */
    public function load(RequestInterface $request)
    {
        $shipment = false;
        $shipmentId = $request->getParam('shipment_id');
        $orderId = $request->getParam('order_id');
        if ($shipmentId) {
            $shipment = $this->shipmentFactory->create()->load($shipmentId);
        } elseif ($orderId) {
            $order = $this->orderFactory->create()->load($orderId);

            /**
             * Check order existing
             */
            if (!$order->getId()) {
                $this->messageManager->addError(__('The order no longer exists.'));
                return false;
            }
            /**
             * Check shipment is available to create separate from invoice
             */
            if ($order->getForcedShipmentWithInvoice()) {
                $this->messageManager->addError(__('Cannot do shipment for the order separately from invoice.'));
                return false;
            }
            /**
             * Check shipment create availability
             */
            if (!$order->canShip()) {
                $this->messageManager->addError(__('Cannot do shipment for the order.'));
                return false;
            }
            $savedQtys = $this->_getItemQtys($request);
            $shipment = $this->orderServiceFactory->create(array('order' => $order))->prepareShipment($savedQtys);

            $tracks = $request->getPost('tracking');
            if ($tracks) {
                foreach ($tracks as $data) {
                    if (empty($data['number'])) {
                        throw new \Magento\Framework\Model\Exception(__('Please enter a tracking number.'));
                    }
                    $track = $this->trackFactory->create()->addData($data);
                    $shipment->addTrack($track);
                }
            }
        }

        $this->registry->register('current_shipment', $shipment);
        return $shipment;
    }
}
