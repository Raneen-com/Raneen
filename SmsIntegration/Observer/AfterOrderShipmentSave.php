<?php

namespace Raneen\SmsIntegration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Raneen\SmsIntegration\Helper\SendMessages;
use Raneen\SmsIntegration\Helper\Sms;

class AfterOrderShipmentSave implements ObserverInterface
{
    protected $logger;
    protected $smsHelper;
    protected $smsTriggerHelper;

    public function __construct(
        LoggerInterface$logger,
        SendMessages $smsHelper,
        Sms $smsTriggerHelper
    ) {
        $this->logger = $logger;
        $this->smsHelper = $smsHelper;
        $this->smsTriggerHelper = $smsTriggerHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();

        $flag = false;
        $message = '';
        $trigger = '';

        if ($order->getBillingAddress()->getTelephone()) {
            $this->logger->critical('Order Status ' . $order->getState());
            $this->logger->critical('telephone ' . $order->getBillingAddress()->getTelephone());

            if ($shipment->getShipmentStatus() == 3 && $this->smsTriggerHelper->getCanceledShipmentSmsEnabled($order->getStoreId())) {
                $trigger = "Shipment Canceled";
                $message = $this->smsTriggerHelper->getCanceledShipmentSmsText($order->getStoreId());
                $flag = true;
            }

            if ($shipment->getShipmentStatus() == 4 && $this->smsTriggerHelper->getShippedShipmentSmsEnabled($order->getStoreId())) {
                $trigger = "Shipment Shipped";
                $message = $this->smsTriggerHelper->getShippedShipmentSmsText($order->getStoreId());
                $flag = true;
            }

            if ($flag) {
                $data = $this->smsTriggerHelper->getShipmentData($order, $shipment);
                $message = $this->smsTriggerHelper->messageProcessor($message, $data);
                $this->smsHelper->singleSmsCURL($message, $order->getBillingAddress()->getTelephone(), $trigger);
            }

        }


    }
}
