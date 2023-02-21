<?php
/**
 * SMSGlobal SMS Integration with Magento developed by SMSGlobal Team (Allam Praveen)
 * Copyright (C) 2018  SMSGlobal
 *
 * This file included in Smsglobal/Sms is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Raneen\SmsIntegration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Smsglobal\Sms\Logger\Logger as Logger;

class Sms extends AbstractHelper
{
    protected $objectInterface;
    protected $objectManager;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_timezoneInterface;

    const CHAR_MAP = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTWXYZ0123456789';

    public function __construct(Context $context, Logger $logger, \Magento\Framework\Stdlib\DateTime\DateTime $timezoneInterface)
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->objectInterface = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->logger = $logger;
        $this->_timezoneInterface = $timezoneInterface;
        parent::__construct($context);
    }

    /**
     * Returns whether new order sms is enabled or not
     * @return boolean
     */
    public function getCompleteOrderSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/completeorder/enabled');
    }

    /**
     * Returns New Order SMS Text from Store Configuration
     * @return string
     */
    public function getCompleteOrderSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/completeorder/smstext');
    }

    public function getConfirmedOrderSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/confirmedorder/enabled');
    }

    /**
     * Returns New Order SMS Text from Store Configuration
     * @return string
     */
    public function getConfirmedOrderSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/confirmedorder/smstext');
    }

    /**
     * Returns whether order refund sms is enabled or not
     * @return boolean
     */
    public function getRefundOrderSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/refundorder/enabled');
    }

    /**
     * Returns order refund SMS Text from Store Configuration
     * @return string
     */
    public function getRefundOrderSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/refundorder/smstext');
    }

    /**
     * Returns whether order cancel sms is enabled or not
     * @return boolean
     */
    public function getCancelOrderSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/ordercancel/enabled');
    }

    /**
     * Returns order cancel SMS Text from Store Configuration
     * @return string
     */
    public function getCancelOrderSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/ordercancel/smstext');
    }

    /**
     * Returns whether new shipment sms is enabled or not
     * @return boolean
     */
    public function getShippedShipmentSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/shipmentshipped/enabled');
    }

    /**
     * Returns New Shipment SMS Text from Store Configuration
     * @return string
     */
    public function getShippedShipmentSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/shipmentshipped/smstext');
    }

    /**
     * Returns whether new shipment sms is enabled or not
     * @return boolean
     */
    public function getCanceledShipmentSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/shipmentcancelled/enabled');
    }

    /**
     * Returns New Shipment SMS Text from Store Configuration
     * @return string
     */
    public function getCanceledShipmentSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/shipmentcancelled/smstext');
    }

    /**
     * @param $message
     * @param $data
     *
     * @return string
     */
    public function messageProcessor($message, $data)
    {
        foreach ($data as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $message;
    }

    /**
     * @param $order
     *
     * @return array
     */
    public function getOrderData($order)
    {
        $data = [];
        $data['OrderNumber'] = $order->getIncrementId();
        $data['CustomerFirstName'] = $order->getCustomerFirstname();
        $data['CustomerLastName'] = $order->getCustomerLastname();
        $data['OrderTotal'] = number_format($order->getGrandTotal(), 2);
        $data['CustomerEmail'] = $order->getCustomerEmail();
        $data['OrderCurrency'] = $order->getOrderCurrencyCode();
        $data['OrderDate'] = date('F j, Y', strtotime($order->getCreatedAt()));
        $data['OrderTime'] = date('g:i a', strtotime($order->getCreatedAt()));
        $data['OrderStatus'] = $order->getStatus();

        return $data;
    }

    public function getConfirmedOrderData($order)
    {
        $data = [];
        $orderItems = $order->getItems();
        $orderShipment = $order->getShippingAddress()->getData();
        $orderItemsName = [];
        foreach ($orderItems as $orderItem) {
            $orderItemsName [] = $orderItem->getName();
        }

        $data['OrderNumber'] = $order->getIncrementId();
        $data['OrderItemsCount'] = count($orderItems);
        $data['OrderItemsName'] = implode(",", $orderItemsName);
        $data['OrderShippingAddress'] = 'street is : ' . $orderShipment['street'] . ', City is: ' . $orderShipment['city'];

        return $data;
    }

    /**
     * @param $order
     * @param $shipment
     *
     * @return array
     */
    public function getShipmentData($order, $shipment)
    {
        $data = [];

        $tracksCollection = $shipment->getTracksCollection();

        if ($tracksCollection && $tracksCollection->getItems()) {
            foreach ($tracksCollection->getItems() as $track) {
                $data['TrackingNumber'] = $track->getTrackNumber();
                $data['Carrier'] = $track->getCarrierCode();
            }
        } else {
            $data['TrackingNumber'] = 'No Tracking Number';
            $data['Carrier'] = 'No Carrier';
        }

        return $data;
    }
}
