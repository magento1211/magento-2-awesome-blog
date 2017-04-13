<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/10/2017
 * Time: 11:26 AM
 */
namespace Mageplaza\Blog\Model;

class Sitemap extends \Magento\Sitemap\Model\Sitemap{
	protected $blogDataHelper;
	public function __construct(\Mageplaza\Blog\Helper\Data $blogDataHelper,\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Escaper $escaper, \Magento\Sitemap\Helper\Data $sitemapData, \Magento\Framework\Filesystem $filesystem, \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory, \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory, \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory, \Magento\Framework\Stdlib\DateTime\DateTime $modelDate, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\App\RequestInterface $request, \Magento\Framework\Stdlib\DateTime $dateTime, \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, array $data = [])
	{
		$this->blogDataHelper=$blogDataHelper;
		parent::__construct($context, $registry, $escaper, $sitemapData, $filesystem, $categoryFactory, $productFactory, $cmsFactory, $modelDate, $storeManager, $request, $dateTime, $resource, $resourceCollection, $data);
	}
	public function getBlogPostsSiteMapCollection(){
		$postCollection=$this->blogDataHelper->postfactory->create()->getCollection();
		$postSiteMapCollection=[];
		foreach ($postCollection as $item){
			$postSiteMapCollection[$item->getId()]=new \Magento\Framework\DataObject([
				'id'=>$item->getId(),
				'url'=>'blog/post/'.$item->getUrlKey(),
				'updated_at'=>$item->getUpdatedAt(),
			]);
		}
		return $postSiteMapCollection;
	}
	public function _initSitemapItems()
	{
		$this->_sitemapItems[] = new \Magento\Framework\DataObject(
			[
				'collection' => $this->getBlogPostsSiteMapCollection(),
			]
		);
		parent::_initSitemapItems(); // TODO: Change the autogenerated stub
	}
}