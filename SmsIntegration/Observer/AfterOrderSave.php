<?php

namespace Raneen\SmsIntegration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Raneen\SmsIntegration\Helper\SendMessages;
use Raneen\SmsIntegration\Helper\Sms;

class AfterOrderSave implements ObserverInterface
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
        $order = $observer->getEvent()->getOrder();
        $flag = false;
        $message = '';
        $trigger = '';
        $data = null;

        if ($order->getBillingAddress()->getTelephone()) {
            $this->logger->critical('Order Status ' . $order->getState());
            $this->logger->critical('telephone ' . $order->getBillingAddress()->getTelephone());

            if ($order->getState() == "new" && $this->smsTriggerHelper->getNewOrderSmsEnabled($order->getStoreId())) {
                $trigger = "New Order";
                $message = $this->smsTriggerHelper->getNewOrderSmsText($order->getStoreId());
                $data = $this->smsTriggerHelper->getOrderData($order);
                $flag = true;
            }

            if ($order->getStatus() == "complete" && $this->smsTriggerHelper->getCompleteOrderSmsEnabled($order->getStoreId())) {
                $trigger = "Order Completed";
                $message = $this->smsTriggerHelper->getCompleteOrderSmsText($order->getStoreId());
                $data = $this->smsTriggerHelper->getOrderData($order);
                $flag = true;
            }

            if ($order->getStatus() == "confirmed" && $this->smsTriggerHelper->getConfirmedOrderSmsEnabled($order->getStoreId())) {
                $trigger = "Confirmed Order";
                $message = $this->smsTriggerHelper->getConfirmedOrderSmsText($order->getStoreId());
                $data = $this->smsTriggerHelper->getConfirmedOrderData($order);
                $flag = true;
            }

            if ($flag) {
                $data['CustomerTelephone'] = $order->getBillingAddress()->getTelephone();
                $message = $this->smsTriggerHelper->messageProcessor($message, $data);
                $this->smsHelper->singleSmsCURL($message, $order->getBillingAddress()->getTelephone(), $trigger);
            }
        }
    }
}
