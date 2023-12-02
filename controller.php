<?php

namespace Concrete\Package\BooleanRich;

use Concrete\Core\Attribute\Category\CategoryService;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Database\EntityManager\Provider\ProviderAggregateInterface;
use Concrete\Core\Database\EntityManager\Provider\StandardPackageProvider;
use Concrete\Core\Package\Package;
use Gettext\Translations;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends Package implements ProviderAggregateInterface
{
    protected $pkgHandle = 'boolean_rich';

    protected $pkgVersion = '1.1.1';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::$appVersionRequired
     */
    protected $appVersionRequired = '8.5.2';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getPackageName()
     */
    public function getPackageName()
    {
        return t('Boolean (Rich Text)');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getPackageDescription()
     */
    public function getPackageDescription()
    {
        return t('Provide a boolean (checkbox) attribute with a Rich Text label.');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::install()
     */
    public function install()
    {
        parent::install();
        $this->installContentFile('config/install.xml');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::upgrade()
     */
    public function upgrade()
    {
        parent::upgrade();
        $this->installContentFile('config/install.xml');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Database\EntityManager\Provider\ProviderAggregateInterface::getEntityManagerProvider()
     */
    public function getEntityManagerProvider()
    {
        return new StandardPackageProvider($this->app, $this, []);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getTranslatableStrings()
     */
    public function getTranslatableStrings(Translations $translations)
    {
        $config = $this->app->make(Repository::class);
        if (version_compare($config->get('concrete.version'), '9') < 0) {
            $categoryService = $this->app->make(CategoryService::class);
            foreach ($categoryService->getList() as $categoryEntity) {
                $category = $categoryEntity->getAttributeKeyCategory();
                foreach ($category->getList() as $key) {
                    if ($key->getAttributeTypeHandle() === 'boolean_rich') {
                        $settings = $key->getAttributeKeySettings();
                        $label = (string) $settings->getCheckboxLabel();
                        if ($label !== '') {
                            $translations->insert('AttributeKeyLabel', $label);
                        }
                    }
                }
            }
        }
    }
}
