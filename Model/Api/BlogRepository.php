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
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Model\Api;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\Blog\Api\BlogRepositoryInterface;
use Mageplaza\Blog\Api\Data\CategoryInterface;
use Mageplaza\Blog\Api\Data\CommentInterface;
use Mageplaza\Blog\Api\Data\PostInterface;
use Mageplaza\Blog\Api\Data\TagInterface;
use Mageplaza\Blog\Api\Data\TopicInterface;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\CommentFactory;
use Mageplaza\Blog\Model\PostLikeFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Class PostRepositoryInterface
 * @package Mageplaza\Blog\Model\Api
 */
class BlogRepository implements BlogRepositoryInterface
{
    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var CommentFactory
     */
    protected $_commentFactory;

    /**
     * @var PostLikeFactory
     */
    protected $_likeFactory;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * BlogRepository constructor.
     *
     * @param Data $helperData
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CommentFactory $commentFactory
     * @param PostLikeFactory $likeFactory
     * @param RequestInterface $request
     * @param DateTime $date
     */
    public function __construct(
        Data $helperData,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CollectionProcessorInterface $collectionProcessor,
        CommentFactory $commentFactory,
        PostLikeFactory $likeFactory,
        RequestInterface $request,
        DateTime $date
    ) {
        $this->_request                     = $request;
        $this->_helperData                  = $helperData;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->date                         = $date;
        $this->_commentFactory              = $commentFactory;
        $this->_likeFactory                 = $likeFactory;
        $this->collectionProcessor          = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function getAllPost()
    {
        $collection = $this->_helperData->getPostCollection();

        return $collection->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getPostView($postId)
    {
        return $this->_helperData->getFactoryByType()->create()->load($postId);
    }

    /**
     * @inheritDoc
     */
    public function getPostViewByAuthorName($authorName)
    {
        $author = $this->_helperData->getFactoryByType('author')->create()->getAuthorByName($authorName);

        return $this->_helperData->getFactoryByType()->create()->getCollection()
            ->addFieldToFilter('author_id', $author->getId())->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getPostViewByAuthorId($authorId)
    {
        return $this->_helperData->getFactoryByType()->create()->getCollection()
            ->addFieldToFilter('author_id', $authorId)->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getPostComment($postId)
    {
        return $this->_commentFactory->create()->getCollection()
            ->addFieldToFilter('post_id', $postId)->getItems();
    }

    /**
     * @return DataObject[]|BlogRepositoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getAllComment()
    {
        $collection = $this->_commentFactory->create()->getCollection();

        return $collection->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getCommentView($commentId)
    {
        return $this->$this->_commentFactory->create()->load($commentId);
    }

    /**
     * @inheritDoc
     */
    public function getCommentList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_commentFactory->create()->getCollection();

        return $this->getListEntity($collection, $searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getPostByTagName($tagName)
    {
        $tag = $this->_helperData->getFactoryByType('tag')->create()->getCollection()
            ->addFieldToFilter('name', $tagName)->getFirstItem();

        return $tag->getSelectedPostsCollection()->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getProductByPost($postId)
    {
        $post = $this->_helperData->getFactoryByType()->create()->load($postId);

        return $post->getSelectedProductsCollection()->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getPostRelated($postId)
    {
        $post = $this->_helperData->getFactoryByType()->create()->load($postId);

        return $post->getRelatedPostsCollection() ? $post->getRelatedPostsCollection()->getItems() : [];
    }

    /**
     * @inheritDoc
     */
    public function addCommentInPost($postId, $commentData)
    {
        $comment = $this->_commentFactory->create();
        $commentData->setPostId($postId);
        $commentData->setIsReply(0);
        $commentData->setReplyId(0);
        $commentData->setCreatedAt($this->date->date());
        $comment->setData($commentData->getData())->save();

        return $comment;
    }

    /**
     * @inheritDoc
     */
    public function getPostLike($postId)
    {
        return $this->_likeFactory->create()->getCollection()
            ->addFieldToFilter('post_id', $postId)->count();
    }

    /**
     * @inheritDoc
     */
    public function getPostList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_helperData->getPostCollection();

        return $this->getListEntity($collection, $searchCriteria);
    }

    /**
     * @param \Mageplaza\Blog\Api\Data\PostInterface $post
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface
     */
    public function createPost($post)
    {
        $data = $post->getData();

        if ($this->checkPostData($data)) {
            $this->prepareData($data);
            $post->addData($data);
            $post->save();
        }

        return $post;
    }

    /**
     * @param string $postId
     *
     * @return string|null
     * @throws Exception
     */
    public function deletePost($postId)
    {
        $post = $this->_helperData->getFactoryByType()->create()->load($postId);

        if ($post) {
            $post->delete();

            return true;
        }

        return false;
    }

    /**
     * @param string $postId
     * @param \Mageplaza\Blog\Api\Data\PostInterface $post
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface|void
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function updatePost($postId, $post)
    {
        if (empty($postId)) {
            throw new InputException(__('Invalid post id %1', $postId));
        }
        $subPost = $this->_helperData->getFactoryByType()->create()->load($postId);

        if (!$subPost->getId()) {
            throw new NoSuchEntityException(
                __(
                    'The "%1" Post doesn\'t exist.',
                    $postId
                )
            );
        }

        $subPost->addData($post->getData())->save();

        return $subPost;
    }

    /**
     * @inheritDoc
     */
    public function getAllTag()
    {
        $collection = $this->_helperData->getFactoryByType('tag')->create()->getCollection();

        return $this->getAllItem($collection);
    }

    /**
     * @inheritDoc
     */
    public function getTagList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_helperData->getFactoryByType('tag')->create()->getCollection();

        return $this->getListEntity($collection, $searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getTagView($tagId)
    {
        return $this->_helperData->getFactoryByType('tag')->create()->load($tagId);
    }

    /**
     * @param \Mageplaza\Blog\Api\Data\TagInterface $tag
     *
     * @return \Mageplaza\Blog\Api\Data\TagInterface
     */
    public function createTag($tag)
    {
        if (!empty($tag->getName())) {
            if (empty($tag->getStoreIds())) {
                $tag->setStoreIds(0);
            }
            if (empty($tag->getEnabled())) {
                $tag->setEnabled(1);
            }
            if (empty($tag->getCreatedAt())) {
                $tag->setCreatedAt($this->date->date());
            }
            if (empty($tag->getMetaRobots())) {
                $tag->setMetaRobots('INDEX,FOLLOW');
            }
            $tag->save();
        }

        return $tag;
    }

    /**
     * @param string $tagId
     *
     * @return bool|string
     * @throws Exception
     */
    public function deleteTag($tagId)
    {
        $tag = $this->_helperData->getFactoryByType('tag')->create()->load($tagId);

        if ($tag) {
            $tag->delete();

            return true;
        }

        return false;
    }

    /**
     * @param string $tagId
     * @param \Mageplaza\Blog\Api\Data\TagInterface $tag
     *
     * @return \Mageplaza\Blog\Api\Data\TagInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function updateTag($tagId, $tag)
    {
        if (empty($tagId)) {
            throw new InputException(__('Invalid tag id %1', $tagId));
        }
        $subTag = $this->_helperData->getFactoryByType('tag')->create()->load($tagId);

        if (!$subTag->getId()) {
            throw new NoSuchEntityException(
                __(
                    'The "%1" Tag doesn\'t exist.',
                    $tagId
                )
            );
        }

        $subTag->addData($tag->getData())->save();

        return $subTag;
    }

    /**
     * @return DataObject[]|BlogRepositoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getAllTopic()
    {
        $collection = $this->_helperData->getFactoryByType('topic')->create()->getCollection();

        return $collection->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getTopicView($topicId)
    {
        return $this->_helperData->getFactoryByType('topic')->create()->load($topicId);
    }

    /**
     * @inheritDoc
     */
    public function getTopicList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_helperData->getFactoryByType('topic')->create()->getCollection();

        return $this->getListEntity($collection, $searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getPostsByTopic($topicId)
    {
        $topic = $this->_helperData->getFactoryByType('topic')->create()->load($topicId);

        return $topic->getSelectedPostsCollection()->getItems();
    }

    /**
     * @param \Mageplaza\Blog\Api\Data\TopicInterface $topic
     *
     * @return \Mageplaza\Blog\Api\Data\TopicInterface
     */
    public function createTopic($topic)
    {
        if (!empty($topic->getName())) {
            if (empty($topic->getStoreIds())) {
                $topic->setStoreIds(0);
            }
            if (empty($topic->getEnabled())) {
                $topic->setEnabled(1);
            }
            if (empty($topic->getCreatedAt())) {
                $topic->setCreatedAt($this->date->date());
            }
            if (empty($topic->getMetaRobots())) {
                $topic->setMetaRobots('INDEX,FOLLOW');
            }
            $topic->save();
        }

        return $topic;
    }

    /**
     * @param string $topicId
     *
     * @return bool|string
     * @throws Exception
     */
    public function deleteTopic($topicId)
    {
        $topic = $this->_helperData->getFactoryByType('topic')->create()->load($topicId);

        if ($topic) {
            $topic->delete();

            return true;
        }

        return false;
    }

    /**
     * @param string $topicId
     * @param \Mageplaza\Blog\Api\Data\TopicInterface $topic
     *
     * @return \Mageplaza\Blog\Api\Data\TopicInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function updateTopic($topicId, $topic)
    {
        if (empty($topicId)) {
            throw new InputException(__('Invalid topic id %1', $topicId));
        }
        $subTopic = $this->_helperData->getFactoryByType('topic')->create()->load($topicId);

        if (!$subTopic->getId()) {
            throw new NoSuchEntityException(
                __(
                    'The "%1" Topic doesn\'t exist.',
                    $topicId
                )
            );
        }

        $subTopic->addData($topic->getData())->save();

        return $subTopic;
    }

    /**
     * @inheritDoc
     */
    public function getCategoryList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_helperData->getFactoryByType('category')->create()->getCollection();

        return $this->getListEntity($collection, $searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getAllCategory()
    {
        $collection = $this->_helperData->getFactoryByType('category')->create()->getCollection();

        return $collection->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getPostsByCategoryId($categoryId)
    {
        $category = $this->_helperData->getFactoryByType('category')->create()->load($categoryId);

        return $category->getSelectedPostsCollection()->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getPostsByCategory($categoryKey)
    {
        $category = $this->_helperData->getFactoryByType('category')->create()->getCollection()
            ->addFieldToFilter('url_key', $categoryKey)->getFirstItem();

        return $category->getSelectedPostsCollection()->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getCategoriesByPostId($postId)
    {
        $post = $this->_helperData->getFactoryByType()->create()->load($postId);

        return $post->getSelectedCategoriesCollection()->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getCategoryView($categoryId)
    {
        return $this->_helperData->getFactoryByType('category')->create()->load($categoryId);
    }

    /**
     * @param \Mageplaza\Blog\Api\Data\CategoryInterface $category
     *
     * @return \Mageplaza\Blog\Api\Data\CategoryInterface
     */
    public function createCategory($category)
    {
        if (!empty($category->getName())) {
            if (empty($category->getStoreIds())) {
                $category->setStoreIds(0);
            }
            if (empty($category->getEnabled())) {
                $category->setEnabled(1);
            }
            if (empty($category->getCreatedAt())) {
                $category->setCreatedAt($this->date->date());
            }
            if (empty($category->getMetaRobots())) {
                $category->setMetaRobots('INDEX,FOLLOW');
            }
            if (empty($category->getParentId())) {
                $category->setParentId(1);
            }
            $category->save();
        }

        return $category;
    }

    /**
     * @param string $categoryId
     *
     * @return bool|string
     * @throws Exception
     */
    public function deleteCategory($categoryId)
    {
        $category = $this->_helperData->getFactoryByType('category')->create()->load($categoryId);

        if ($category) {
            $category->delete();

            return true;
        }

        return false;
    }

    /**
     * @param string $categoryId
     * @param \Mageplaza\Blog\Api\Data\CategoryInterface $category
     *
     * @return \Mageplaza\Blog\Api\Data\CategoryInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function updateCategory($categoryId, $category)
    {
        if (empty($categoryId)) {
            throw new InputException(__('Invalid category id %1', $categoryId));
        }
        $subCategory = $this->_helperData->getFactoryByType('category')->create()->load($categoryId);

        if (!$subCategory->getId()) {
            throw new NoSuchEntityException(
                __(
                    'The "%1" Category doesn\'t exist.',
                    $categoryId
                )
            );
        }

        $subCategory->addData($category->getData())->save();

        return $subCategory;
    }

    /**
     * @return DataObject[]|BlogRepositoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getAuthorList()
    {
        $collection = $this->_helperData->getFactoryByType('author')->create()->getCollection();

        return $collection->getItems();
    }

    /**
     * @param \Mageplaza\Blog\Api\Data\AuthorInterface $author
     *
     * @return \Mageplaza\Blog\Api\Data\AuthorInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createAuthor($author)
    {
        $collection = $this->_helperData->getFactoryByType('author')->create()->getCollection();

        if (!empty($author->getCustomerId())) {
            $customerId = $author->getCustomerId();
            $collection->addFieldToFilter('customer_id', $customerId);
            $customer = $this->_customerRepositoryInterface->getById($customerId);
            if (!$customer || $collection->count() > 0) {
                return null;
            }
        }

        if (!empty($author->getName())) {
            if (empty($author->getType())) {
                $author->setType(0);
            }
            if (empty($author->getStatus())) {
                $author->setStatus(0);
            }
            if (empty($author->getCreatedAt())) {
                $author->setCreatedAt($this->date->date());
            }
            $author->save();
        }

        return $author;
    }

    /**
     * @param string $authorId
     *
     * @return bool|string
     * @throws Exception
     */
    public function deleteAuthor($authorId)
    {
        $author = $this->_helperData->getFactoryByType('author')->create()->load($authorId);

        if ($author) {
            $author->delete();

            return true;
        }

        return false;
    }

    /**
     * @param string $authorId
     * @param \Mageplaza\Blog\Api\Data\AuthorInterface $author
     *
     * @return \Mageplaza\Blog\Api\Data\AuthorInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function updateAuthor($authorId, $author)
    {
        if (empty($authorId)) {
            throw new InputException(__('Invalid author id %1', $authorId));
        }
        $subAuthor = $this->_helperData->getFactoryByType('author')->create()->load($authorId);

        if (!$subAuthor->getId()) {
            throw new NoSuchEntityException(
                __(
                    'The "%1" Author doesn\'t exist.',
                    $authorId
                )
            );
        }

        $subAuthor->addData($author->getData())->save();

        return $subAuthor;
    }

    /**
     * @param array $data
     */
    protected function prepareData(&$data)
    {
        if (!empty($data['categories_ids'])) {
            $data['categories_ids'] = explode(',', $data['categories_ids']);
        }
        if (!empty($data['tags_ids'])) {
            $data['tags_ids'] = explode(',', $data['tags_ids']);
        }
        if (!empty($data['topics_ids'])) {
            $data['topics_ids'] = explode(',', $data['topics_ids']);
        }
        if (empty($data['enabled'])) {
            $data['enabled'] = 0;
        }
        if (empty($data['allow_comment'])) {
            $data['allow_comment'] = 0;
        }
        if (empty($data['store_ids'])) {
            $data['store_ids'] = 0;
        }
        if (empty($data['in_rss'])) {
            $data['in_rss'] = 0;
        }
        if (empty($data['meta_robots'])) {
            $data['meta_robots'] = 'INDEX,FOLLOW';
        }
        if (empty($data['layout'])) {
            $data['layout'] = 'empty';
        }
        $data['created_at'] = $this->date->date();

        if (empty($data['publish_date'])) {
            $data['publish_date'] = $this->date->date();
        }
    }

    /**
     * @param $data
     *
     * @return bool
     */
    protected function checkPostData($data)
    {
        if (empty($data['name']) || empty($data['author_id']) || !$this->checkAuthor($data['author_id'])) {
            return false;
        }

        if (!empty($data['categories_ids'])) {
            $collection = $this->_helperData->getFactoryByType('category')->create()->getCollection();
            foreach (explode(',', $data['categories_ids']) as $id) {
                if ($collection->addFieldToFilter('category_id', $id)->count() < 1) {
                    return false;
                }
            }
        }

        if (!empty($data['tags_ids'])) {
            $collection = $this->_helperData->getFactoryByType('tag')->create()->getCollection();
            foreach (explode(',', $data['tags_ids']) as $id) {
                if ($collection->addFieldToFilter('tag_id', $id)->count() < 1) {
                    return false;
                }
            }
        }

        if (!empty($data['topics_ids'])) {
            $collection = $this->_helperData->getFactoryByType('topic')->create()->getCollection();
            foreach (explode(',', $data['topics_ids']) as $id) {
                if ($collection->addFieldToFilter('topic_id', $id)->count() < 1) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param $authorId
     *
     * @return bool
     */
    protected function checkAuthor($authorId)
    {
        $collection = $this->_helperData->getFactoryByType('author')->create()->getCollection()
            ->addFieldToFilter('user_id', $authorId);

        return $collection->count() > 0 ? true : false;
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection $searchResult
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return mixed
     */
    protected function getListEntity($searchResult, $searchCriteria)
    {
        $this->collectionProcessor->process($searchCriteria, $searchResult);
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     */
    /**
     * @param $collection
     *
     * @return PostInterface[]|CategoryInterface[]|TagInterface[]|TopicInterface[]|CommentInterface[]
     */
    protected function getAllItem($collection)
    {
        $page = $this->_request->getParam('page', 1);
        $limit = $this->_request->getParam('limit', 10);

        $collection->getSelect()->limitPage($page, $limit);

        return $collection->getItems();
    }
}