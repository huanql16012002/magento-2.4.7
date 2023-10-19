<?php

namespace Wtm\Custom\Block\Dashboard\Orders;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Module\Manager;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Grid extends \Magento\Backend\Block\Dashboard\Orders\Grid
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    public function __construct(StoreManagerInterface $storeManager, Context $context, Data $backendHelper, Manager $moduleManager, CollectionFactory $collectionFactory, array $data = [])
    {
        parent::__construct($context, $backendHelper, $moduleManager, $collectionFactory, $data);
        $this->_storeManager = $storeManager;
    }

    /**
     * Process collection after loading
     *
     * @return $this
     */
    protected function _afterLoadCollection()
    {
        foreach ($this->getCollection() as $item) {
            $item->getCustomer() ?: $item->setCustomer('Guest');
        }
        return $this;
    }

    /**
     * Prepare columns.
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'customer',
            ['header' => __('Customer'), 'sortable' => false, 'index' => 'customer', 'default' => __('Guest')]
        );

        $this->addColumn(
            'items',
            [
                'header' => __('Tickets'),
                'type' => 'number',
                'sortable' => false,
                'index' => 'items_count'
            ]
        );

        $baseCurrencyCode = $this->_storeManager->getStore((int)$this->getParam('store'))->getBaseCurrencyCode();

        $this->addColumn(
            'total',
            [
                'header' => __('Total'),
                'sortable' => false,
                'type' => 'currency',
                'currency_code' => $baseCurrencyCode,
                'index' => 'revenue'
            ]
        );

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }
}
?>
