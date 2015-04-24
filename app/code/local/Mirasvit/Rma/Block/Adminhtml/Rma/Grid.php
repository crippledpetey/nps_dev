<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   RMA
 * @version   1.0.9
 * @build     742
 * @copyright Copyright (C) 2015 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_Rma_Block_Adminhtml_Rma_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_customFilters = array();
    public function __construct()
    {
        parent::__construct();
        $this->setId('rma_grid');
        $this->setDefaultSort('updated_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    public function addCustomFilter($field, $filter) {
        $this->_customFilters[$field] = $filter;
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('rma/rma')
            ->getCollection();
        foreach ($this->_customFilters as $key => $value) {
            $collection->addFieldToFilter($key, $value);
        }
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $columns = Mage::getSingleton('rma/config')->getGeneralRmaGridColumns();

        if (in_array('increment_id', $columns)) {
            $this->addColumn('increment_id', array(
                    'header' => Mage::helper('rma')->__('RMA #'),
                    'index' => 'increment_id',
                    'filter_index' => 'main_table.increment_id',
                )
            );
        }
        if (in_array('order_increment_id', $columns)) {
            $this->addColumn('order_increment_id', array(
                'header'    => Mage::helper('rma')->__('Order #'),
                'index'     => 'order_increment_id',
                'filter_index'     => 'order.increment_id',
                )
            );
        }
        if (in_array('name', $columns)) {
            $this->addColumn('name', array(
                'header'    => Mage::helper('rma')->__('Customer Name'),
                'index'     => 'name',
                )
            );
        }
        if (in_array('user_id', $columns)) {
            $this->addColumn('user_id', array(
                'header'    => Mage::helper('rma')->__('Owner'),
                'index'     => 'user_id',
                'filter_index'     => 'main_table.user_id',
                'type'      => 'options',
                'options'   => Mage::helper('rma')->getAdminUserOptionArray(),
                )
            );
        }
        if (in_array('last_reply_name', $columns)) {
            $this->addColumn('last_reply_name', array(
                'header'    => Mage::helper('rma')->__('Last Replier'),
                'index'     => 'last_reply_name',
                'filter_index'     => 'main_table.last_reply_name',
                'frame_callback'   => array($this, '_lastReplyFormat'),
                )
            );
        }
        if (in_array('status_id', $columns)) {
            $this->addColumn('status_id', array(
                'header'    => Mage::helper('rma')->__('Status'),
                'index'     => 'status_id',
                'filter_index'     => 'main_table.status_id',
                'type'      => 'options',
                'options'   => Mage::getModel('rma/status')->getCollection()->getOptionArray(),
                )
            );
        }
        if (in_array('increment_id', $columns)) {
            $this->addColumn('created_at', array(
                'header'    => Mage::helper('rma')->__('Created Date'),
                'index'     => 'created_at',
                'filter_index'     => 'main_table.created_at',
                'type'      => 'datetime',
                )
            );
        }
        if (in_array('updated_at', $columns)) {
            $this->addColumn('updated_at', array(
                'header'    => Mage::helper('rma')->__('Last Activity'),
                'index'     => 'updated_at',
                'filter_index'     => 'main_table.updated_at',
                'type'      => 'datetime',
                'frame_callback'   => array($this, '_lastActivityFormat'),
                )
            );
        }
        if (in_array('store_id', $columns)) {
            $this->addColumn('store_id', array(
                    'header' => Mage::helper('rma')->__('Store'),
                    'index' => 'store_id',
                    'filter_index' => 'main_table.store_id',
                    'type' => 'options',
                    'options' => Mage::helper('rma')->getCoreStoreOptionArray(),
                )
            );
        }
        if ($this->getTabMode() || in_array('action', $columns)) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('rma')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('rma')->__('View'),
                            'url'     => array(
                                'base'=>'rmaadmin/adminhtml_rma/edit',
                            ),
                            'field'   => 'id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                ));
        }

        $collection = Mage::helper('rma/field')->getStaffCollection();
        foreach ($collection as $field) {
            if (in_array($field->getCode(), $columns)) {
                $this->addColumn($field->getCode(), array(
                    'header'           => Mage::helper('rma')->__($field->getName()),
                    'index'            => $field->getCode(),
                    'type'             => $field->getGridType(),
                    'options'          => $field->getGridOptions(),
                ));
            }
        }

        if ($this->getExportVisibility() !== false) {
            $this->addExportType('*/*/exportCsv', Mage::helper('rma')->__('CSV'));
            $this->addExportType('*/*/exportXml', Mage::helper('rma')->__('XML'));
        }

        return parent::_prepareColumns();
    }

    public function _lastReplyFormat($renderedValue, $row, $column, $isExport)
    {
        $name = $row['last_reply_name'];
        if (!$row['is_admin_read']) {
            $name .= ' <img src="'.$this->getSkinUrl('images/fam_newspaper.gif').'">';
        }

        return $name;
    }


    public function _lastActivityFormat($renderedValue, $row, $column, $isExport)
    {
        return Mage::helper('rma/string')->nicetime(strtotime($row['updated_at']));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rma_id');
        $this->getMassactionBlock()->setFormFieldName('rma_id');
        $statuses = array(
                array('label'=>'', 'value'=>''),
                array('label'=>$this->__('Disabled'), 'value'=> 0),
                array('label'=>$this->__('Enabled'), 'value'=> 1),
        );
        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('rma')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('rma')->__('Are you sure?')
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('rmaadmin/adminhtml_rma/edit', array('id' => $row->getId()));
    }

    /************************/

}