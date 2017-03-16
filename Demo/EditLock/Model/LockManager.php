<?php

namespace Demo\EditLock\Model;

class LockManager
{

    protected $_connection;
    protected $_logger;

    protected $_mainTable = 'locked_product';

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_connection = $resourceConnection->getConnection();
        $this->_logger = $logger;
    }

    public function isProductLocked($productId = null, $userId = null)
    {
        if (!$productId || !$userId) {
            return false;
        }

        $select = $this->_connection->select()->from(
            $this->_connection->getTableName($this->_getMainTable())
        )
        ->where('product_id = ?', (int) $productId)
        ->where($this->_connection->prepareSqlCondition('user_id', ['neq' => (int) $userId]));

        try {
            $result = $this->_connection->fetchRow($select);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

        return !empty($result) ? true : false;
    }

    public function lockProduct($productId = null, $userId = null)
    {
        if (!$productId || !$userId) {
            return false;
        }

        try {
            $this->_connection->insertOnDuplicate($this->_getMainTable(), [
                'product_id'    => (int) $productId,
                'user_id'       => (int) $userId
            ]);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return $this;
    }

    public function unLockProduct($userId = null)
    {
        try {
            $this->_connection->delete($this->_getMainTable(), [
                'user_id'       => (int) $userId
            ]);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return $this;
    }

    protected function _getMainTable()
    {
        return $this->_connection->getTableName($this->_mainTable);
    }

}
