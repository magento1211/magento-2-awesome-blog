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
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\Blog\Block\MonthlyArchive;

use Mageplaza\Blog\Block\Frontend;

/**
 * Class Listpost
 * @package Mageplaza\Blog\Block\MonthlyArchive
 */
class Listpost extends Frontend
{
	/**
	 * @return array|string
	 */
    public function getPostList()
    {
        return $this->getBlogPagination(\Mageplaza\Blog\Helper\Data::MONTHLY, $this->getCurrentUrl());
    }

	/**
	 * @return string
	 */
    public function checkRss()
    {
        return $this->helperData->getBlogUrl('post/rss');
    }

	/**
	 * @return mixed
	 */
    public function getCurrentUrl()
    {
        $currentUrl = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        $arr = explode('/', $currentUrl);
        $result = end($arr);
        if (strpos($result, '?') !== false) {
            $arr = explode('?', $result);
            $result = reset($arr);
        }
        return $result;
    }
}
