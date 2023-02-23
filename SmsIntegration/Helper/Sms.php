<?php

namespace Raneen\SmsIntegration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
class Sms extends AbstractHelper
{
protected $objectInterface;
    protected $objectManager;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_timezoneInterface;

    const CHAR_MAP = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTWXYZ0123456789';

    public function __construct(Context $context, \Magento\Framework\Stdlib\DateTime\DateTime $timezoneInterface)
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->objectInterface = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_timezoneInterface = $timezoneInterface;
        parent::__construct($context);
    }

    /**
     * Returns whether new order sms is enabled or not
     * @return boolean
     */
    public function getNewOrderSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/orderTriggerGroup/neworder/enabled');
    }

    /**
     * Returns New Order SMS Text from Store Configuration
     * @return string
     */
    public function getNewOrderSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/orderTriggerGroup/neworder/smstext');
    }

    /**
     * Returns whether new order sms is enabled or not
     * @return boolean
     */
    public function getCompleteOrderSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/orderTriggerGroup/completeorder/enabled');
    }

    /**
     * Returns New Order SMS Text from Store Configuration
     * @return string
     */
    public function getCompleteOrderSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/orderTriggerGroup/completeorder/smstext');
    }

    public function getConfirmedOrderSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/orderTriggerGroup/confirmedorder/enabled');
    }

    /**
     * Returns New Order SMS Text from Store Configuration
     * @return string
     */
    public function getConfirmedOrderSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/orderTriggerGroup/confirmedorder/smstext');
    }

    /**
     * Returns whether order refund sms is enabled or not
     * @return boolean
     */
    public function getRefundOrderSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/orderTriggerGroup/refundorder/enabled');
    }

    /**
     * Returns order refund SMS Text from Store Configuration
     * @return string
     */
    public function getRefundOrderSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/orderTriggerGroup/refundorder/smstext');
    }

    /**
     * Returns whether order cancel sms is enabled or not
     * @return boolean
     */
    public function getCancelOrderSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/orderTriggerGroup/ordercancel/enabled');
    }

    /**
     * Returns order cancel SMS Text from Store Configuration
     * @return string
     */
    public function getCancelOrderSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/orderTriggerGroup/ordercancel/smstext');
    }

    /**
     * Returns whether new shipment sms is enabled or not
     * @return boolean
     */
    public function getShippedShipmentSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/shipmentTriggerGroup/shipmentshipped/enabled');
    }

    /**
     * Returns New Shipment SMS Text from Store Configuration
     * @return string
     */
    public function getShippedShipmentSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/shipmentTriggerGroup/shipmentshipped/smstext');
    }

    /**
     * Returns whether new shipment sms is enabled or not
     * @return boolean
     */
    public function getCanceledShipmentSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/shipmentTriggerGroup/shipmentcancelled/enabled');
    }

    /**
     * Returns New Shipment SMS Text from Store Configuration
     * @return string
     */
    public function getCanceledShipmentSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/shipmentTriggerGroup/shipmentcancelled/smstext');
    }

    /**
     * Returns whether Abandoned Cart Reminder sms is enabled or not
     * @return boolean
     */
    public function getAbandonedCartReminderSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/abandonedcartreminder/enabled');
    }

    /**
     * Returns Abandoned Cart Reminder SMS Text from Store Configuration
     * @return string
     */
    public function getAbandonedCartReminderSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/abandonedcartreminder/smstext');
    }

    /**
     * Returns Abandoned Cart Reminder Time SMS Text from Store Configuration
     * @return string
     */
    public function getAbandonedCartReminderTimeSms()
    {
        return $this->objectInterface->getValue('sms_triggers/abandonedcartreminder/abandonedcartremindertime');
    }

    /**
     * Returns whether Payment Reminder sms is enabled or not
     * @return boolean
     */
    public function getPaymentReminderSmsEnabled()
    {
        return $this->objectInterface->getValue('sms_triggers/paymentreminders/enabled');
    }

    /**
     * Returns Payment Reminder SMS Text from Store Configuration
     * @return string
     */
    public function getPaymentReminderSmsText()
    {
        return $this->objectInterface->getValue('sms_triggers/paymentreminders/smstext');
    }

    /**
     * Returns Payment Reminder Time SMS Text from Store Configuration
     * @return string
     */
    public function getPaymentReminderTimeSms()
    {
        return $this->objectInterface->getValue('sms_triggers/paymentreminders/paymentremindertime');
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

    public function getAbandonedCartData($quote)
    {
        $data = [];
        $data['OrderItemsCount'] = $quote['items_count'];
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
