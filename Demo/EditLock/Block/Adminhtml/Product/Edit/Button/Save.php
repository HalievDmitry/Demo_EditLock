<?php

namespace Demo\EditLock\Block\Adminhtml\Product\Edit\Button;

use Magento\Ui\Component\Control\Container;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;

class Save extends \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Button\Save
{

	protected $_session;

	public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Backend\Model\Auth\Session $session
    ) {
		$this->_session = $session;
    	parent::__construct($context, $registry);
    }

	public function getButtonData()
    {
        if ($this->getProduct()->isReadonly()) {
            return [];
        }

        $isLocked = $this->_session->getData('lock_save');

        return [
            'label' => __('Save'),
            'class' => sprintf('save primary%s', $isLocked ? ' disabled' : ''),
            'button_class' => $isLocked ? 'disabled' : '',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => $this->getSaveTarget(),
                                'actionName' => $this->getSaveAction(),
                                'params' => [
                                    false
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'class_name' => Container::SPLIT_BUTTON,
            'options' => $this->getOptions(),
        ];
    }

}
