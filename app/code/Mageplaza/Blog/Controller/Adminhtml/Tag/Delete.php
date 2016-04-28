<?php
/**
 * Mageplaza_Blog extension
 *                     NOTICE OF LICENSE
 * 
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 * 
 *                     @category  Mageplaza
 *                     @package   Mageplaza_Blog
 *                     @copyright Copyright (c) 2016
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Mageplaza\Blog\Controller\Adminhtml\Tag;

class Delete extends \Mageplaza\Blog\Controller\Adminhtml\Tag
{
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('tag_id');
        if ($id) {
            $name = "";
            try {
                /** @var \Mageplaza\Blog\Model\Tag $tag */
                $tag = $this->tagFactory->create();
                $tag->load($id);
                $name = $tag->getName();
                $tag->delete();
                $this->messageManager->addSuccess(__('The Tag has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_mageplaza_blog_tag_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
                $resultRedirect->setPath('mageplaza_blog/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_mageplaza_blog_tag_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('mageplaza_blog/*/edit', ['tag_id' => $id]);
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('Tag to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('mageplaza_blog/*/');
        return $resultRedirect;
    }
}
