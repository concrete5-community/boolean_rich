<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Attribute\View $view
 * @var Concrete\Package\BooleanRich\Attribute\BooleanRich\Controller $controller
 * @var string $akCheckboxLabel
 * @var bool $akCheckedByDefault
 * @var bool $checked
 * @var string $coreVersion
 */

if (version_compare($coreVersion, '9') < 0) {
    ?>
    <div class="checkbox">
        <label>
            <input type="checkbox" value="1" name="<?= $view->field('value') ?>"<?= $checked ? ' checked="checked"' : '' ?> />
            <?= $controller->getCheckboxLabel() ?>
        </label>
    </div>
    <?php
} else {
    ?>
    <div class="form-check ccm-boolean-rich">
        <input class="form-check-input" type="checkbox" value="1" name="<?= $view->field('value') ?>" id="<?= $view->field('value') ?>"<?= $checked ? ' checked="checked"' : '' ?> />
        <label class="form-check-label" for="<?= $view->field('value') ?>">
            <?= $controller->getCheckboxLabel() ?>
        </label>
    </div>
    <?php
}
