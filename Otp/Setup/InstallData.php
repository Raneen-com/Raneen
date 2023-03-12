<?php
namespace Raneen\Otp\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;




class InstallData implements InstallDataInterface
{
    protected $eavSetupFactory;
    protected $eavConfig;
    protected $customerSetupFactory;


    public function __construct(EavSetupFactory $eavSetupFactory, Config $eavConfig, CustomerSetupFactory $customerSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig       = $eavConfig;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerSetup->addAttribute(Customer::ENTITY, 'customer_mobile_number', [
                'type'         => 'varchar',
                'label'        => 'Customer Mobile Number',
                'input'        => 'text',
                'required'     => false,
                'visible'      => true,
                'user_defined' => true,
                'position'     => 999,
                'system'       => 0,
            ]
        );
        $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'customer_mobile_number')
            ->addData(['used_in_forms' => [
                'adminhtml_customer',
                'customer_account_create',
                'customer_account_edit'
            ]
            ]);

        $attribute->save();
    }
}
