<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-11 23:15:05
 * @@Modify Date: 2018-03-08 14:04:12
 * @@Function:
 */

namespace Magiccart\Magicproduct\Model\System\Config;

class Types implements \Magento\Framework\Option\ArrayInterface
{

    const ALL        = '0';
    const BEST 		 = 'bestseller';
    const FEATURED 	 = 'featured';
    const LATEST     = 'latest';
    const MOSTVIEWED = 'mostviewed';
    const NEWPRODUCT = 'new';
    const RANDOM 	 = 'random';
    const RECENTLY 	 = 'recently';
    const REVIEW     = 'review';
    const SALE 	     = 'sale';
    const SPECIAL 	 = 'special';

    public function toArray()
    {
        return [
            self::BEST 		 =>   __('Mais Vendidos'),
            self::FEATURED 	 =>   __('Featured Products'),
            self::LATEST   	 =>  __('Latest Products'),
            // self::MOSTVIEWED => __('Most Viewed'),
            self::NEWPRODUCT => __('New Products'),
            self::RANDOM   	 =>   __('Produtos Aleatórios'),
            // self::RECENTLY   =>   __('Recently Viewed'),
            self::SALE =>   __('Sale Products'),
        ];
    }

    public function toOptionArray()
    {
        return [
            [ 'value' =>  self::BEST, 		'label' =>   __('Mais Vendidos') ],
            [ 'value' =>  self::FEATURED, 	'label' =>   __('Featured Products') ],
            [ 'value' =>  self::LATEST, 	'label' =>   __('Latest Products') ],
            // [ 'value' =>  self::MOSTVIEWED, 'label' =>   __('Most Viewed') ],
            [ 'value' =>  self::NEWPRODUCT, 'label' =>   __('New Products') ],
            [ 'value' =>  self::RANDOM, 	'label' =>   __('Produtos Aleatórios') ],
            [ 'value' =>  self::RECENTLY, 	'label' =>   __('Recently Viewed') ],
            [ 'value' =>  self::SALE, 		'label' =>   __('Sale Products') ],
        ];
    }

    public function toOptionAll()
    {
        return [
            [ 'value' =>  self::ALL, 		'label' =>   __('All') ],
            [ 'value' =>  self::BEST, 		'label' =>   __('Mais Vendidos') ],
            [ 'value' =>  self::FEATURED, 	'label' =>   __('Featured Products') ],
            [ 'value' =>  self::LATEST, 	'label' =>   __('Latest Products') ],
            // [ 'value' =>  self::MOSTVIEWED, 'label' =>   __('Most Viewed') ],
            [ 'value' =>  self::NEWPRODUCT, 'label' =>   __('New Products') ],
            [ 'value' =>  self::RANDOM, 	'label' =>   __('Produtos Aleatórios') ],
            // [ 'value' =>  self::RECENTLY, 	'label' =>   __('Recently Viewed') ],
            [ 'value' =>  self::SALE, 		'label' =>   __('Sale Products') ],
        ];
    }

}
