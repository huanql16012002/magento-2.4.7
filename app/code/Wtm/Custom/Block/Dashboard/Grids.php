<?php

namespace Wtm\Custom\Block\Dashboard;

use Magento\Backend\Block\Dashboard\Tab\Products\Ordered;

class Grids extends \Magento\Backend\Block\Dashboard\Grids
{
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Json\EncoderInterface $jsonEncoder, \Magento\Backend\Model\Auth\Session $authSession, array $data = [])
    {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }
    /**
     * Prepare layout for dashboard bottom tabs
     *
     * To load block statically:
     *     1) content must be generated
     *     2) url should not be specified
     *     3) class should not be 'ajax'
     * To load with ajax:
     *     1) do not load content
     *     2) specify url (BE CAREFUL)
     *     3) specify class 'ajax'
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        // load this active tab statically
        $this->addTab(
            'ordered_products',
            [
                'label' => __('Bestsellers'),
                'content' => $this->getLayout()->createBlock(
                    Ordered::class
                )->toHtml(),
                'active' => true
            ]
        );

        // load other tabs with ajax
        $this->addTab(
            'reviewed_products',
            [
                'label' => __('Most Viewed Events'),
                'url' => $this->getUrl('adminhtml/*/productsViewed', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        //$this->addTab(
        //    'new_customers',
        //    [
        //        'label' => __('New Customers'),
        //        'url' => $this->getUrl('adminhtml/*/customersNewest', ['_current' => true]),
        //        'class' => 'ajax'
        //    ]
        //);
        //
        //$this->addTab(
        //    'customers',
        //    [
        //        'label' => __('Customers'),
        //        'url' => $this->getUrl('adminhtml/*/customersMost', ['_current' => true]),
        //        'class' => 'ajax'
        //    ]
        //);

        return parent::_prepareLayout();
    }
}
?>
