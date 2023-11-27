<?php

namespace Concrete\Package\BooleanRich\Attribute\BooleanRich;

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Attribute\Boolean\Controller as BaseController;
use Concrete\Core\Attribute\FontAwesomeIconFormatter;
use Concrete\Core\Editor\LinkAbstractor;
use SimpleXMLElement;

class Controller extends BaseController
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::getIconFormatter()
     */
    public function getIconFormatter()
    {
        return new FontAwesomeIconFormatter('check-double');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Attribute\Boolean\Controller::type_form();
     */
    public function type_form()
    {
        parent::type_form();
        $this->set('editor', $this->app->make('editor'));
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Attribute\Boolean\Controller::saveKey();
     */
    public function saveKey($data)
    {
        $type = parent::saveKey($data);
        /** @var \Concrete\Core\Entity\Attribute\Key\Settings\BooleanSettings $type */
        $type->setCheckboxLabel(LinkAbstractor::translateTo($type->getCheckboxLabel()));

        return $type;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Attribute\Boolean\Controller::getCheckboxLabel()
     */
    public function getCheckboxLabel()
    {
        if ($this->akCheckboxLabel) {
            return LinkAbstractor::translateFrom(tc('AttributeKeyLabel', $this->akCheckboxLabel));
        }

        return $this->attributeKey->getAttributeKeyDisplayName('html');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Attribute\Boolean\Controller::exportKey()
     */
    public function exportKey($akey)
    {
        $this->load();
        $type = $akey->addChild('type');
        $type->addAttribute('checked-by-default', $this->akCheckedByDefault);
        $type->addChild('label', htmlspecialchars(LinkAbstractor::export((string) $this->akCheckboxLabel), ENT_XML1));

        return $akey;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Attribute\Boolean\Controller::importKey()
     */
    public function importKey(SimpleXMLElement $akey)
    {
        $type = $this->getAttributeKeySettings();
        if (isset($akey->type)) {
            $type->setIsCheckedByDefault(filter_var((string) $akey->type['checked'], FILTER_VALIDATE_BOOLEAN));
            $type->setCheckboxLabel(LinkAbstractor::import((string) $akey->type->label));
        }

        return $type;
    }
}
