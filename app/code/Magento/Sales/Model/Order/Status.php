<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Framework\Model\Exception;

/**
 * Class Status
 *
 * @method string getStatus()
 * @method string getLabel()
 */
class Status extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\Store\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Store\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Store\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_storeManager = $storeManager;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Order\Status');
    }

    /**
     * Assign order status to particular state
     *
     * @param string $state
     * @param bool $isDefault make the status as default one for state
     * @param bool $visibleOnFront
     * @return $this
     * @throws \Exception
     */
    public function assignState($state, $isDefault = false, $visibleOnFront = false)
    {
        /** @var \Magento\Sales\Model\Resource\Order\Status $resource */
        $resource = $this->_getResource();
        $resource->beginTransaction();
        try {
            $resource->assignState($this->getStatus(), $state, $isDefault, $visibleOnFront);
            $resource->commit();
        } catch (\Exception $e) {
            $resource->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * @param string $state
     * @return void
     * @throws Exception
     */
    protected function validateBeforeUnassign($state)
    {
        if ($this->getResource()->checkIsStateLast($state)) {
            throw new Exception(__('The last status can\'t be unassigned from its current state.'));
        }
        if ($this->getResource()->checkIsStatusUsed($this->getStatus())) {
            throw new Exception(__('Status can\'t be unassigned, because it is used by existing order(s).'));
        }
    }

    /**
     * Unassigns order status from particular state
     *
     * @param string $state
     * @return $this
     * @throws \Exception
     */
    public function unassignState($state)
    {
        $this->validateBeforeUnassign($state);
        $this->getResource()->unassignState($this->getStatus(), $state);
        $this->_eventManager->dispatch(
            'sales_order_status_unassign',
            [
                'status' => $this->getStatus(),
                'state' => $state
            ]
        );
        return $this;
    }

    /**
     * Getter for status labels per store
     *
     * @return array
     */
    public function getStoreLabels()
    {
        if ($this->hasData('store_labels')) {
            return $this->_getData('store_labels');
        }
        $labels = $this->_getResource()->getStoreLabels($this);
        $this->setData('store_labels', $labels);
        return $labels;
    }

    /**
     * Get status label by store
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return string
     */
    public function getStoreLabel($store = null)
    {
        $store = $this->_storeManager->getStore($store);
        $labels = $this->getStoreLabels();
        if (isset($labels[$store->getId()])) {
            return $labels[$store->getId()];
        } else {
            return __($this->getLabel());
        }
    }

    /**
     * Load default status per state
     *
     * @param string $state
     * @return $this
     */
    public function loadDefaultByState($state)
    {
        $this->load($state, 'default_state');
        return $this;
    }
}
