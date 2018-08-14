<?php

namespace SM\Performance\Command;

use Magento\Catalog\Model\Product\Visibility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DummySku extends Command {

    protected $productFactory;
    private   $taxClassCollectionFactory;

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxClassCollectionFactory,
        \Magento\Framework\App\State $state,
        $name = null
    ) {
        parent::__construct($name);
        $this->productFactory            = $productFactory;
        $this->taxClassCollectionFactory = $taxClassCollectionFactory;
        //$state->setAreaCode('frontend');
    }

    protected function configure() {
        $this->setName("retail:dummysku");
        $this->setDescription("A command the programmer was too lazy to enter a description for.");
        $this->addOption(
            'iterations',
            null,
            InputOption::VALUE_REQUIRED,
            'How many times should the sku be added?',
            1
        );
        $this->addOption(
            'sku',
            null,
            InputOption::VALUE_REQUIRED,
            'Sku name prefix?',
            'dummySKU'
        );
        $this->addOption(
            'storeId',
            null,
            InputOption::VALUE_REQUIRED,
            'Store ID?',
            1
        );
        $this->addOption(
            'cloneProduct',
            null,
            InputOption::VALUE_REQUIRED,
            'Clone from product ID?',
            1
        );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            $output->writeln("Start dummy product");
            $iterations = $input->getOption('iterations');
            if (is_numeric($iterations) && $iterations > 0) {
                for ($i = 0; $i < $iterations; $i++) {
                    $product = $this->getProductModel();
                    $product->addData($this->getProductRandom($input->getOption('cloneProduct')));
                    $product->setId(null);
                    $product
                        ->setUrlKey(uniqid('dummyUrlKey_'))
                        ->setStoreId($input->getOption('storeId'))
                        ->setWebsiteIds([1])//website ID the product is assigned to, as an array
                        //->setAttributeSetId(9)//ID of a attribute set named 'default'
                        //->setTypeId('simple')//product type
                        ->setCreatedAt(strtotime('now'))//product creation time
                        ->setSku(uniqid($input->getOption('sku') . '_'))//SKU
                        ->setName(uniqid('dummy_product' . '_'))//product name
                        ->setWeight(4.0000)
                        ->setStatus(1)//product status (1 - enabled, 2 - disabled)
                        ->setTaxClassId($this->getRandomTaxClassId())//tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                        ->setVisibility(Visibility::VISIBILITY_BOTH)//catalog and search visibility
                        ->setManufacturer(28)//manufacturer id
                        ->setColor(24)
                        ->setNewsFromDate('06/26/2016')//product set as new from
                        ->setNewsToDate('06/30/2018')//product set as new to
                        ->setCountryOfManufacture('AF')//country of manufacture (2-letter country code)

                        ->setPrice(rand(20, 1000))//price in form 11.22
                        ->setCost(rand(20, 1000))//price in form 11.22
                        ->setSpecialPrice(rand(20, 1000))//special price in form 11.22
                        ->setSpecialFromDate('06/1/2014')//special price from (MM-DD-YYYY)
                        ->setSpecialToDate('06/30/2018')//special price to (MM-DD-YYYY)
                        ->setMsrpEnabled(1)//enable MAP
                        ->setMsrpDisplayActualPriceType(
                            1)//display actual price (1 - on gesture, 2 - in cart, 3 - before order confirmation, 4 - use config)
                        ->setMsrp(99.99)//Manufacturer's Suggested Retail Price

                        ->setMetaTitle('test meta title 2')
                        ->setMetaKeyword('test meta keyword 2')
                        ->setMetaDescription('test meta description 2')
                        ->setDescription('This is a long description')
                        ->setShortDescription('This is a short description')
                        ->setMediaGallery(['images' => [], 'values' => []])//media gallery initialization
                        //->addImageToMediaGallery(
                        //    'media/catalog/product/1/0/10243-1.png',
                        //    ['image', 'thumbnail', 'small_image'],
                        //    false,
                        //    false)//assigning image, thumb and small image to media gallery

                        ->setStockData(
                            [
                                'use_config_manage_stock' => 0, //'Use config settings' checkbox
                                'manage_stock'            => 1, //manage stock
                                'min_sale_qty'            => 1, //Minimum Qty Allowed in Shopping Cart
                                'max_sale_qty'            => 2, //Maximum Qty Allowed in Shopping Cart
                                'is_in_stock'             => 1, //Stock Availability
                                'qty'                     => 999 //qty
                            ]
                        )//->setCategoryIds([3, 10]); //assign product to categories
                    ;
                    $product->save();
                    if ($i % 100 === 0)
                        $output->writeln('<info>added product with sku: ' . $product->getSku() . ' - num of' . ($i + 1) . '</info>');
                }
            }
        }
        catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_cloneProduct;

    protected function getProductRandom($cloneProductId) {
        if (is_null($this->_cloneProduct)) {
            $this->_cloneProduct = $this->getProductModel()->load($cloneProductId);

            if (!$this->_cloneProduct->getId()) {
                throw new \Exception("check clone product id");
            }
        }

        return $this->_cloneProduct->getData();
    }

    /**
     * @return  \Magento\Catalog\Model\Product
     */
    protected function getProductModel() {
        return $this->productFactory->create();
    }

    protected $taxClass;

    protected function getRandomTaxClassId() {
        if (is_null($this->taxClass)) {
            $this->taxClass = [];
            /** @var  \Magento\Tax\Model\ResourceModel\TaxClass\Collection $collection */
            $collection = $this->taxClassCollectionFactory->create();
            foreach ($collection as $item) {
                if ($item->getData('class_type') === 'PRODUCT') {
                    $this->taxClass[] = $item->getData('id');
                }
            }
        }

        return $this->taxClass[array_rand($this->taxClass)];
    }

} 