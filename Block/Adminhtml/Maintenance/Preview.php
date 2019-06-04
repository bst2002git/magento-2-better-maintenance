<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_BetterMaintenance
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\BetterMaintenance\Block\Adminhtml\Maintenance;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Mageplaza\BetterMaintenance\Block\Maintenance;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\BetterMaintenance\Helper\Data as HelperData;
use Mageplaza\BetterMaintenance\Helper\Image as HelperImage;
use Magento\Cms\Block\Block;
use Mageplaza\BetterMaintenance\Block\Adminhtml\Clock;
use Magento\Newsletter\Block\Subscribe;
use Magento\Customer\Block\Form\Register;

/**
 * Class Preview
 * @package Mageplaza\BetterMaintenance\Block\Adminhtml\Maintenance
 */
class Preview extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_BetterMaintenance::maintenance/preview.phtml';

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Maintenance
     */
    protected $_maintenanceBlock;

    /**
     * @var UrlRewriteFactory
     */
    protected $_urlRewrite;

    /**
     * @var HelperImage
     */
    protected $_helperImage;

    /**
     * Preview constructor.
     *
     * @param PageFactory $pageFactory
     * @param HelperData $helperData
     * @param Maintenance $maintenanceBlock
     * @param UrlRewriteFactory $urlRewrite
     * @param HelperImage $helperImage
     * @param Context $context
     */
    public function __construct(
        PageFactory $pageFactory,
        HelperData $helperData,
        Maintenance $maintenanceBlock,
        UrlRewriteFactory $urlRewrite,
        HelperImage $helperImage,
        Context $context
    ) {
        $this->_pageFactory      = $pageFactory;
        $this->_helperData       = $helperData;
        $this->_maintenanceBlock = $maintenanceBlock;
        $this->_urlRewrite       = $urlRewrite;
        $this->_helperImage      = $helperImage;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getFormData()
    {
        $newData = [];
        $data    = $this->_request->getParam('formData');
        $data    = urldecode($data);
        $data    = explode('&', $data);
        foreach ($data as $value) {
            $val              = explode('=', $value);
            $val[0]           = $this->filterKey($val[0]);
            $newData[$val[0]] = urldecode($val[1]);
        }

        return $newData;
    }

    /**
     * @return array
     */
    public static function cleanValue()
    {
        return [
            '[groups]',
            'groups',
            '[clock_setting]',
            '[display_setting]',
            '[fields]',
            '[value]',
            '[subscribe_setting]',
            '[social_contact]',
            '[maintenance_setting]',
            '[comingsoon_setting]',
            '[general]'
        ];
    }

    /**
     * @param $arr
     *
     * @return mixed
     */
    public function filterKey($arr)
    {
        foreach (self::cleanValue() as $word) {
            $arr = str_replace($word, '', $arr);
        }

        return $arr;
    }

    /**
     * @param $logo
     *
     * @return string
     */
    public function getLogo($logo)
    {
        return $this->_maintenanceBlock->getLogo($logo);
    }

    /**
     * @return array
     */
    public function getSocialList()
    {
        $list    = [
            '[social_facebook]',
            '[social_twitter]',
            '[social_instagram]',
            '[social_google]',
            '[social_youtube]',
            '[social_pinterest]'
        ];
        $url     = [];
        $imgPath = 'Mageplaza_BetterMaintenance::media/';
        foreach ($list as $value) {
            $filterValue = preg_replace('/[^A-Za-z0-9\_]/', '', $value);
            $url[]       = [
                'link' => $this->getFormData()[$value],
                'img'  => $this->getViewFileUrl($imgPath . $filterValue . '.png')
            ];
        }

        return $url;
    }

    /**
     * @return string|null
     * @throws LocalizedException
     */
    public function getBlockCms()
    {
        $blockId = $this->getFormData()['[footer_block][cms_block]'];
        if ($blockId === '0') {
            return null;
        }
        $block = $this->getLayout()->createBlock(Block::class)
            ->setBlockId($blockId)
            ->toHtml();

        return $block;
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getClockBlock()
    {
        $block = $this->getLayout()
            ->createBlock(Clock::class)
            ->setTemplate('Mageplaza_BetterMaintenance::clock/timer.phtml')
            ->toHtml();

        return $block;
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getSubscribeBlock()
    {
        $block = $this->getLayout()
            ->createBlock(Subscribe::class)
            ->setData('area', 'frontend')
            ->setTemplate('Magento_Newsletter::subscribe.phtml')
            ->toHtml();

        return $block;
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getRegisterBlock()
    {
        $block = $this->getLayout()
            ->createBlock(Register::class)
            ->setData('area', 'frontend')
            ->setTemplate('form/register.phtml')
            ->toHtml();

        return $block;
    }

    /**
     * @param $image
     *
     * @return string|null
     */
    public function getImageUrl($image)
    {
        return $this->_maintenanceBlock->getImageUrl($image);
    }

    /**
     * @param $video
     *
     * @return string|null
     */
    public function getVideoUrl($video)
    {
        return $this->_maintenanceBlock->getVideoUrl($video);
    }

    /**
     * @param $type
     *
     * @return array
     */
    public function getListImages($type)
    {
        $list = [];
        foreach ($this->getFormData() as $key => $value) {
            if (strpos($key, $type) && strpos($key, '[file]')) {
                $list[] = $value;
            }
        }

        return $list;
    }

    /**
     * @param $type
     *
     * @return array
     */
    public function getMultipleImagesUrl($type)
    {
        $images = $this->getListImages($type);
        $urls   = [];
        foreach ($images as $image) {
            $urls[] = $this->_helperImage->getMediaUrl($this->_helperImage->getMediaPath($image));
        }

        return $urls;
    }
}