<?php

namespace Raneen\SmsIntegration\Cron;

use \Psr\Log\LoggerInterface as Logger;

class AbandonedCartAndPaymentCron
{
    protected $logger;
    protected $quotesFactory;
    protected $customerRepository;
    protected $addressRepository;

    public function __construct(
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quotesFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface            $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface             $addressRepository,
        Logger                                                       $logger
    )
    {
        $this->quotesFactory = $quotesFactory;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
    }


    public function execute()
    {
        $this->logger->info('SMS Send Reminder Cron Initiated', []);
        $quoteObject = $this->quotesFactory->create();
        $quotesCollection = $quoteObject->addFieldToFilter('is_active', array('eq' => 1))
            ->addFieldToFilter('customer_id', array('neq' => null));

        foreach ($quotesCollection->getData() as $quote) {
            $customer = $this->customerRepository->getById($quote['customer_id']);
            $billingAddressId = $customer->getDefaultBilling();
            $shippingAddressId = $customer->getDefaultShipping();
            $telephone = '';

            if(!empty($billingAddressId))
            {
                $billingAddress = $this->addressRepository->getById($billingAddressId);
                $telephone = $billingAddress->getTelephone();
            }elseif(!empty($shippingAddressId))
            {
                $billingAddress = $this->addressRepository->getById($shippingAddressId);
                $telephone = $billingAddress->getTelephone();
            }
        }
    }
}
