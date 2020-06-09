<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2018-12-15 10:54:47
 * @@Function:
 */

namespace Magiccart\Magicproduct\Block\Product;

class GridProduct extends \Magento\Catalog\Block\Product\AbstractProduct
{

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_objectManager;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var _stockconfig
     */
    protected $_stockConfig;

     /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $_stockFilter;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
	
    protected $_limit; // Limit Product

    /**
     * @param Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        \Magento\CatalogInventory\Model\Configuration $stockConfig,
        array $data = []
    ) {
        $this->urlHelper = $urlHelper;
        $this->_objectManager = $objectManager;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_stockFilter = $stockFilter;
        $this->_stockConfig = $stockConfig;
        parent::__construct( $context, $data );
    }

    public function getTypeFilter()
    {
        $type = $this->escapeHtml($this->getRequest()->getPost('type'));
        if(!$type){
            $type = $this->getActivated(); // get form setData in Block
        }
        return $type;
    }

    public function getWidgetCfg($cfg=null)
    {
        $info = $this->getRequest()->getPost('info');
        if($info){
            if(isset($info[$cfg])) return $info[$cfg];
            return $info;          
        }else {
            $info = $this->getCfg();
            if(isset($info[$cfg])) return $info[$cfg];
            return $info;
        }
    }

    public function getLoadedProductCollection()
    {
            $this->_limit = (int) $this->getWidgetCfg('limit');
            $type = $this->getTypeFilter();
            $fn = 'get' . ucfirst($type) . 'Products';
            $collection = $this->{$fn}();
            if ($this->_stockConfig->isShowOutOfStock() != 1) {
                $this->_stockFilter->addInStockFilterToCollection($collection);
            }
            $this->_eventManager->dispatch(
                'catalog_block_product_list_collection',
                ['collection' => $collection]
            );

        return $collection;
    }


    public function getBestsellerProducts(){
		/* Cach 1 */
		//$collection = $this->_objectManager->create('\Magento\Reports\Model\ResourceModel\Report\Collection\Factory');
		//$collection = $collection->create('Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection');
        /* Cach 2 */
		$collection = $this->_objectManager->get('\Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory')->create()->setModel('Magento\Catalog\Model\Product');
        /* End Cach 2 */
		$collection->setPageSize($this->_limit)->setCurPage(1);
        $producIds = array();
        foreach ($collection as $product) {
            $producIds[] = $product->getProductId();
        }

        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter()->addAttributeToFilter('entity_id', array('in' => $producIds));
		
        return $collection;
        
    }

    public function getFeaturedProducts()
    {

        // // cach 1 
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // //$data= array();
        // //$this->_objectManager->create($this->_instanceName, $data);
        // $collection = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');

        // // Cach 2
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $manager */
        // $manager = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        // $collection = $manager->create();
        
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $collection->addAttributeToFilter('featured', '1')
                    ->addStoreFilter()
                    ->addAttributeToSelect('*')
                    ->addMinimalPrice()
                    ->addFinalPrice()
                    ->addTaxPercents()
                    ->setPageSize($this->_limit)->setCurPage(1);;

        return $collection;

    }

    public function getLatestProducts(){

        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter()
        ->addAttributeToSort('entity_id', 'desc')
        ->setPageSize($this->_limit)->setCurPage(1);

        return $collection; 
    }

    public function getMostviewedProducts(){
 
        $collection = $this->_objectManager->get('\Magento\Reports\Model\ResourceModel\Report\Product\Viewed\CollectionFactory')->create()->setModel('Magento\Catalog\Model\Product');
        $collection->setPageSize($this->_limit)->setCurPage(1);
		$producIds = array();
        foreach ($collection as $product) {
            $producIds[] = $product->getProductId();
        }

        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter()->addAttributeToFilter('entity_id', array('in' => $producIds));
        return $collection;

    }

    public function getNewProducts() {

        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter()->addAttributeToFilter(
            'news_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $todayEndOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            'news_to_date',
            [
                'or' => [
                    0 => ['date' => true, 'from' => $todayStartOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            [
                ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
                ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
            ]
        )->addAttributeToSort('news_from_date', 'desc')
        ->setPageSize($this->_limit)->setCurPage(1);

        return $collection;
    }

    public function getRandomProducts() {

        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter();

        $collection->getSelect()->order('rand()');


        // getNumProduct
        $collection->setPageSize($this->_limit)->setCurPage(1);
        return $collection;
    }

    public function getRecentlyProducts() {

        $block = $this->getLayout()->createBlock('Magento\Reports\Block\Product\Viewed');
        if($block){
            $collection = $block->setPageSize($this->_limit)->getItemsCollection();
        } else {
            echo 'nhieu';
            $collection =$this->_objectManager->create('\Magento\Reports\Model\Product\Index\Viewed');
            $collection = $collection->getCollection()
                                ->addAttributeToSelect('*')
                                ->setPageSize($this->_limit)
                                ->setCurPage(1);             
        }
        
        return $collection;
    }

    public function getSaleProducts(){

        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
$collection->joinField(
        'qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left'
    );
        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter()->addAttributeToFilter(
            'special_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $todayEndOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            'special_to_date',
            [
                'or' => [
                    0 => ['date' => true, 'from' => $todayStartOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            [
                ['attribute' => 'special_from_date', 'is' => new \Zend_Db_Expr('not null')],
                ['attribute' => 'special_to_date', 'is' => new \Zend_Db_Expr('not null')],
            ]
        )->addAttributeToSort('special_to_date', 'desc')
        ->setPageSize($this->_limit)->setCurPage(1);

        return $collection;

    }

    public function getSpecialProducts() {


        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter()->addAttributeToFilter(
            'special_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $todayEndOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            'special_to_date',
            [
                'or' => [
                    0 => ['date' => true, 'from' => $todayStartOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            [
                ['attribute' => 'special_from_date', 'is' => new \Zend_Db_Expr('not null')],
                ['attribute' => 'special_to_date', 'is' => new \Zend_Db_Expr('not null')],
            ]
        )->addAttributeToSort('special_to_date', 'desc')
        ->setPageSize($this->_limit)->setCurPage(1);

        return $collection;

    }

    /**
     * Get post parameters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int|null
     */
    public function getProductQty($product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $qty = $stockItem->getQty();
        return $qty > 0 ? $qty : 0;
    }

}
