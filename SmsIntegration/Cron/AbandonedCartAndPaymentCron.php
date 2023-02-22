<?php

namespace Raneen\SmsIntegration\Cron;

use \Psr\Log\LoggerInterface as Logger;

class AbandonedCartAndPaymentCron
{

    protected $logger;
    protected $quotesFactory;

    /**
     * Constructor
     *
     * @param Logger $logger
     * @param \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quotesFactory
     */
    public function __construct(
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quotesFactory,
        Logger                                                       $logger
    )
    {
        $this->logger = $logger;
        $this->quotesFactory = $quotesFactory;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $this->logger->info('SMS Send Reminder Cron Initiated', []);
        $quoteObject = $this->quotesFactory->create();
        $quotesCollection = $quoteObject->addFieldToFilter('is_active', array('eq' => 1));
        dd($quotesCollection->getData());

//        foreach ($smslogCollection as $smslog) {
//            $messageId = $smslog->getMsgId();
//            $status = $this->smsHelper->getSmsStatus($messageId);
//            $this->logger->info('Smsglobal Cron Initiated', [$status]);
//            if ($status) {
//                $smslog->setStatus($status);
//                $smslog->save();
//            }
//        }
    }
}
