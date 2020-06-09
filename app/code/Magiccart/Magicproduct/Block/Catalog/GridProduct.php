<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2018-03-15 00:43:06
 * @@Function:
 */

namespace Magiccart\Magicproduct\Block\Catalog;

class GridProduct extends \Magiccart\Magicproduct\Block\Product\ListProduct
{

    protected $_limit; // Limit Product
    protected $_type; // type is type filter bestseller, featured ...
    protected $_types; // array types is types filter bestseller, featured ...

    public function getTypeFilter() // getTypeFilter
    {
        $type = $this->escapeHtml($this->getRequest()->getParam('type'));
        if(!$type) $type = $this->getActivated(); // get form setData in Block
        return $type;
    }

    public function getWidgetCfg($cfg=null)
    {
        $info = $this->getRequest()->getParam('info');
        if($info){
            if(isset($info[$cfg])) return $info[$cfg];
            return $info;          
        }else {
            $info = $this->getCfg();
            if(isset($info[$cfg])) return $info[$cfg];
            return $info;
        }
    }

    protected function _getProductCollection()
    {

        // if (is_null($this->_productCollection)) {
        //     $type = $this->getTypeFilter();
        //     $category = $this->categoryRepository->get($type);
        //     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //     $registry = $objectManager->get('Magento\Framework\Registry');
        //     $registry->register('current_category', $category, true); //register current category
        //     // $category = $registry->registry('current_category'); //get current category
        //     // $registry->unregister('current_category');  //unregister current category
        // }
        // return parent::_getProductCollection();
        
        // $limit = $this->getWidgetCfg('limit');
        // if (is_null($this->_productCollection)) {
        //     $this->setCategoryId($this->getTypeFilter());
        // }
        // return parent::_getProductCollection()->setPageSize($limit);

        $this->_limit   = (int) $this->getWidgetCfg('limit');
        $this->_type    = $this->getTypeFilter(); // $this->getActivated();
        $isCategory     = is_numeric($this->_type);
        $categoryId = $isCategory ? $this->_type : (int) $this->getWidgetCfg('category_id');
        if (is_null($this->_productCollection)) {
            $this->setCategoryId($categoryId); //$this->setCategoryId($this->getTypeFilter());
            $this->_productCollection = parent::_getProductCollection();   
        }

        // $this->_types = $this->getWidgetCfg('types');
        if($isCategory || !$this->_type) return $this->_productCollection->setPageSize($this->_limit);
        $fn = 'get' . ucfirst( $this->_type);
        $collection = $this->{$fn}($this->_productCollection);
        return $collection->setPageSize($this->_limit);

    }

    public function getBestseller($collection){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $report = $objectManager->get('\Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory')->create();
        $ids = $collection->getAllIds();
        $report->addFieldToFilter('product_id', array('in' => $ids))->setPageSize($this->_limit)->setCurPage(1);
        $producIds = array();
        // $notIds = array();
        foreach ($report as $product) {
            // if(!in_array($product->getProductId(), $ids )) $notIds[] =  $product->getProductId();
            $producIds[] = $product->getProductId();
        }

        $collection->addAttributeToFilter('entity_id', array('in' => $producIds));
    
        return $collection;
        
    }

    public function getFeatured($collection)
    {

        $collection->addAttributeToFilter('featured', '1');

        return $collection;

    }

    public function getLatest($collection){

        $collection = $collection->addStoreFilter()
        ->addAttributeToSort('entity_id', 'desc');

        return $collection;

    }

    public function getMostviewed($collection){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $report = $objectManager->get('\Magento\Reports\Model\ResourceModel\Report\Product\Viewed\CollectionFactory')->create();
        $ids = $collection->getAllIds();
        $report->addFieldToFilter('product_id', array('in' => $ids))->setPageSize($this->_limit)->setCurPage(1);
        $producIds = array();
        foreach ($report as $product) {
            $producIds[] = $product->getProductId();
        }

        $collection->addAttributeToFilter('entity_id', array('in' => $producIds));
    
        return $collection;
    }

    public function getNew($collection) {

        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        $collection = $collection->addStoreFilter()->addAttributeToFilter(
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
        )->addAttributeToSort('news_from_date', 'desc');

        return $collection;
    }

    public function getRandom($collection) {

        $collection->getSelect()->order('rand()');
        return $collection;

    }

    public function getRecently($collection) {

        $ids = $collection->getAllIds();
        $collection = $this->getLayout()->createBlock('Magento\Reports\Block\Product\Viewed');
        if(!$collection){
            $collection = $collection
                        ->setProductIds($ids)
                        ->setPageSize($this->_limit)
                        ->getItemsCollection();
        } else {
            $report = $this->_objectManager->create('\Magento\Reports\Model\Product\Index\Viewed');
            $report = $collection->getCollection()
                                    ->addAttributeToSelect('*')
                                    ->addFieldToFilter('product_id', array('in' => $ids))
                                    ->setPageSize($this->_limit)
                                    ->setCurPage(1);          
        }
        
        return $collection;
    }

    public function getSale($collection){

        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        $collection = $collection->addStoreFilter()->addAttributeToFilter(
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
        )->addAttributeToSort('special_to_date', 'desc');

        return $collection;

    }

}
