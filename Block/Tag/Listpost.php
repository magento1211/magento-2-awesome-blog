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
namespace Mageplaza\Blog\Block\Tag;

use Mageplaza\Blog\Block\Frontend;

/**
 * Class Listpost
 * @package Mageplaza\Blog\Block\Tag
 */
class Listpost extends Frontend
{

	/**
	 * @return array|string
	 */
    public function getPostList()
    {
        return $this->getBlogPagination(\Mageplaza\Blog\Helper\Data::TAG, $this->getRequest()->getParam('id'));
    }

	/**
	 * @return string
	 */
    public function checkRss()
    {
        return $this->helperData->getBlogUrl('post/rss');
    }
}
