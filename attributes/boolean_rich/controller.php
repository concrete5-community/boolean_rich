<?php

namespace Concrete\Package\BooleanRich\Attribute\BooleanRich;

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Attribute\Boolean\Controller as BaseController;
use Concrete\Core\Attribute\FontAwesomeIconFormatter;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Editor\LinkAbstractor;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Http\ResponseFactoryInterface;
use SimpleXMLElement;

class Controller extends BaseController
{
    /**
     * @private
     */
    const MAX_LABEL_LENGTH = 255;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::getIconFormatter()
     */
    public function getIconFormatter()
    {
        return new FontAwesomeIconFormatter(version_compare($this->getCoreVersion(), '9') < 0 ? 'check-circle' : 'check-double');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Attribute\Boolean\Controller::form()
     */
    public function form()
    {
        parent::form();
        $this->set('coreVersion', $this->getCoreVersion());
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Attribute\Boolean\Controller::type_form()
     */
    public function type_form()
    {
        parent::type_form();
        $sets = $this->getSets();
        if ($this->request->isPost() && $this->request->request->has('akCheckboxLabel')) {
            $akCheckboxLabel = $this->request->request->get('akCheckboxLabel');
        } else {
            $akCheckboxLabel = isset($sets['akCheckboxLabel']) ? LinkAbstractor::translateFromEditMode($sets['akCheckboxLabel']) : '';
        }
        $this->set('akCheckboxLabel', $akCheckboxLabel);
        $this->set('akCheckboxLabelLength', function_exists('mb_strlen') ? mb_strlen($akCheckboxLabel) : strlen($akCheckboxLabel));
        $this->set('coreVersion', $this->getCoreVersion());
        $this->set('editor', $this->app->make('editor'));
        $this->set('maxLabelLength', static::MAX_LABEL_LENGTH);
        $this->set('token', $this->app->make('token'));
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Attribute\Boolean\Controller::saveKey()
     */
    public function saveKey($data)
    {
        $type = parent::saveKey($data);
        /** @var \Concrete\Core\Entity\Attribute\Key\Settings\BooleanSettings $type */
        $type->setCheckboxLabel(LinkAbstractor::translateTo((string) $type->getCheckboxLabel()));

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

    public function action_checkLength()
    {
        $token = $this->app->make('token');
        if (!$token->validate('boolean_rich_check_length')) {
            throw new UserMessageException($token->getErrorMessage());
        }
        $text = LinkAbstractor::translateTo((string) $this->request->request->get('label'));
        $length = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);

        return $this->app->make(ResponseFactoryInterface::class)->json($length);
    }

    /**
     * @return string
     */
    private function getCoreVersion()
    {
        return $this->app->make(Repository::class)->get('concrete.version');
    }
}
