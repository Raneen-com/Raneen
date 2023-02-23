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

namespace Raneen\SmsIntegration\Observer;

use Psr\Log\LoggerInterface;
use Raneen\SmsIntegration\Helper\SendMessages;
use Raneen\SmsIntegration\Helper\Sms;

class AfterOrderCreditmemoSave implements \Magento\Framework\Event\ObserverInterface
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

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();

        if ($order->getBillingAddress()->getTelephone()) {
            $this->logger->critical('Order Status ' . $order->getState());
            $this->logger->critical('telephone ' . $order->getBillingAddress()->getTelephone());

            if ($order->getStatus() == "closed" && $this->smsTriggerHelper->getRefundOrderSmsEnabled()) {
                $trigger = "Order Closed";
                $message = $this->smsTriggerHelper->getRefundOrderSmsText();
                $data = $this->smsTriggerHelper->getOrderData($order);
                $data['CustomerTelephone'] = $order->getBillingAddress()->getTelephone();
                $message = $this->smsTriggerHelper->messageProcessor($message, $data);
                $this->smsHelper->singleSmsCURL($message, $order->getBillingAddress()->getTelephone(), $trigger);
            }

        }
    }
}
