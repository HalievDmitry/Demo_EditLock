<?php

namespace Demo\EditLock\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProcessAdminAction implements ObserverInterface
{

    protected $_session;
    protected $_layoutFactory;
    protected $_lockManager;
    protected $_messageManager;

    public function __construct(
        \Magento\Backend\Model\Auth\Session $session,
        \Demo\EditLock\Model\LockManager $lockManager,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_session = $session;
        $this->_lockManager = $lockManager;
        $this->_messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_session->getUser()) {
            return;
        }
        $userId = $this->_session->getUser()->getId();
        if ($observer->getEvent()->getRequest()->getFullActionName() == 'catalog_product_edit') {

            $productId = $observer->getEvent()->getRequest()->getParam('id', null);
            if ($this->_lockManager->isProductLocked($productId, $userId)) {
                $this->_messageManager->addWarning(__('This product is currently editing by another user.'));
                $this->_session->setData('lock_save', true);
            } else {
                $this->_lockManager->lockProduct($productId, $userId);
                $this->_session->setData('lock_save', false);
            }
        } else {
            $this->_lockManager->unLockProduct($userId);
            $this->_session->setData('lock_save', false);
        }
    }

}
