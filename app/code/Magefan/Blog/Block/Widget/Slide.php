<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Widget;

/**
 * Blog sidebar categories block
 */
class Slide extends \Magefan\Blog\Block\Post\PostList\AbstractList implements \Magento\Widget\Block\BlockInterface
{
    // use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'slide_posts';

    /**
     * @return $this
     */
    public function _construct()
    {
        $this->setPageSize((int) $this->getData('limit'));

        if($this->getData('slide')){
            $data['vertical-Swiping'] = $this->getData('vertical');
            $breakpoints = $this->getResponsiveBreakpoints();
            $responsive = '[';
            $num = count($breakpoints);
            foreach ($breakpoints as $size => $opt) {
                $item = (int)  $this->getData($opt);
                $responsive .= '{"breakpoint": "'.$size.'", "settings": {"slidesToShow": "'.$item.'"}}';
                $num--;
                if($num) $responsive .= ', ';
            }
            $responsive .= ']';
            $data['slides-To-Show'] = $this->getData('visible');
            $data['swipe-To-Slide'] = 'true';
            $data['responsive'] = $responsive;
            
            $this->addData($data);
        }

        return parent::_construct();
    }

    /**
     * Retrieve block identities
     * @return array
     */
    public function getIdentities()
    {
        return [\Magento\Cms\Model\Block::CACHE_TAG . '_blog_slide_posts_widget'  ];
    }

    public function getPostedOn($_post, $format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime($_post->getData('publish_time')));
    }

    public function getShorContent($_post)
    {
        $content = $_post->getContent();
        $pageBraker = '<!-- pagebreak -->';
        
        $isMb = function_exists('mb_strpos');
        $p = $isMb ? strpos($content, $pageBraker) : mb_strpos($content, $pageBraker);

        if ($p) {
            $content = substr($content, 0, $p);
        }

        return $this->_filterProvider->getPageFilter()->filter($content);
    }


    public function getResponsiveBreakpoints()
    {
        return array(1201=>'visible', 991=>'desktop', 769=>'tablet', 641=>'landscape', 481=>'portrait', 361=>'mobile', 1=>'mobile');
    }

    public function getSlideOptions()
    {
        return array('autoplay', 'arrows', 'autoplay-Speed', 'dots', 'infinite', 'padding', 'vertical', 'vertical-Swiping', 'responsive', 'rows', 'slides-To-Show', 'swipe-To-Slide');
    }

    public function getFrontendCfg()
    { 
        if($this->getSlide()) return $this->getSlideOptions();

        $this->addData(array('responsive' =>json_encode($this->getGridOptions())));
        return array('padding', 'responsive');

    }

    public function getGridOptions()
    {
        $options = array();
        $breakpoints = $this->getResponsiveBreakpoints(); ksort($breakpoints);
        foreach ($breakpoints as $size => $screen) {
            $options[]= array($size-1 => $this->getData($screen));
        }
        return $options;
    }

}
