<?php

namespace Raneen\SmsIntegration\Cron;

use Psr\Log\LoggerInterface as Logger;
use Raneen\SmsIntegration\Helper\SendMessages;
use Raneen\SmsIntegration\Helper\Sms;

class AbandonedCartAndPaymentCron
{
    protected $logger;
    protected $quotesFactory;
    protected $quotePaymentFactory;
    protected $customerRepository;
    protected $addressRepository;
    protected $smsHelper;
    protected $smsTriggerHelper;

    public function __construct(
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quotesFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface            $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface             $addressRepository,
        Logger                                                       $logger,
        SendMessages $smsHelper,
        Sms $smsTriggerHelper,
        \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory $quotePaymentFactory,
    ) {
        $this->quotesFactory = $quotesFactory;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
        $this->smsHelper = $smsHelper;
        $this->smsTriggerHelper = $smsTriggerHelper;
        $this->quotePaymentFactory = $quotePaymentFactory;
    }

    public function execute()
    {
        //dd("DSAdsadsa");
        $this->logger->info('SMS Send Reminder Cron Initiated', []);
        $quoteObject = $this->quotesFactory->create();
        $quotesCollection = $quoteObject->addFieldToFilter('is_active', ['eq' => 1])
            ->addFieldToFilter('customer_id', ['neq' => null]);

        foreach ($quotesCollection->getData() as $quote) {
            $quotePaymentObject = $this->quotePaymentFactory->create();
            $quotePaymentObjectCollection = $quotePaymentObject->addFieldToFilter('quote_id', ['eq' => $quote['entity_id']]);

            $customer = $this->customerRepository->getById($quote['customer_id']);
            $billingAddressId = $customer->getDefaultBilling();
            $shippingAddressId = $customer->getDefaultShipping();
            $telephone = '';

            if (!empty($billingAddressId)) {
                $billingAddress = $this->addressRepository->getById($billingAddressId);
                $telephone = $billingAddress->getTelephone();
            } elseif (!empty($shippingAddressId)) {
                $billingAddress = $this->addressRepository->getById($shippingAddressId);
                $telephone = $billingAddress->getTelephone();
            }

            preg_match_all('/01\d{9}/', $telephone, $phoneNumber);

            if ($phoneNumber[0]) {
                if ($quotePaymentObjectCollection->getData() == null && $this->smsTriggerHelper->getAbandonedCartReminderSmsEnabled()) {
                    $trigger = "Abandoned Cart Reminder";
                    $message = $this->smsTriggerHelper->getAbandonedCartReminderSmsText();
                    $data = $this->smsTriggerHelper->getAbandonedCartData($quote);
                    $message = $this->smsTriggerHelper->messageProcessor($message, $data);
                    $this->smsHelper->singleSmsCURL($message, '2' . $phoneNumber[0][0], $trigger);
                } elseif ($quotePaymentObjectCollection->getData() && $this->smsTriggerHelper->getPaymentReminderSmsEnabled()) {
                    $trigger = "Payment Reminder";
                    $message = $this->smsTriggerHelper->getPaymentReminderSmsText();
                    $data = $this->smsTriggerHelper->getAbandonedCartData($quote);
                    $message = $this->smsTriggerHelper->messageProcessor($message, $data);
                    $this->smsHelper->singleSmsCURL($message, '2' . $phoneNumber[0][0], $trigger);
                }
            }
        }
    }
}
