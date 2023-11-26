<?php

use Concrete\Core\Editor\CkeditorEditor;
use Concrete\Core\Editor\LinkAbstractor;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Core\Editor\EditorInterface|Concrete\Core\Editor\CkeditorEditor $editor
 * @var bool|null $akCheckedByDefault
 * @var string|null $akCheckboxLabel
 */

$akCheckedByDefault = !empty($akCheckedByDefault);
$akCheckboxLabel = isset($akCheckboxLabel) ? LinkAbstractor::translateFromEditMode((string) $akCheckboxLabel) : '';
?>
<fieldset>
    <legend><?= t('Checkbox Options') ?></legend>
    <div class="form-group">
        <label><?= t('Default Value') ?></label>
        <div class="form-check">
            <?= $form->checkbox('akCheckedByDefault', 1, $akCheckedByDefault) ?>
            <label class="form-check-label" for="akCheckedByDefault"><?= t('The checkbox will be checked by default.') ?></label>
        </div>
    </div>
    <div class="form-group">
        <label><?= t('Label') ?></label>
        <?php
        if ($editor instanceof CkeditorEditor && method_exists($editor, 'outputEditorWithOptions')) {
            echo $editor->outputEditorWithOptions(
                'akCheckboxLabel',
                [
                    'enterMode' => 2, // CKEDITOR.ENTER_BR
                ],
                $akCheckboxLabel
            );
        } else {
            echo $editor->outputStandardEditor('akCheckboxLabel', $akCheckboxLabel);
        }
        ?>
        <p class="help-block"><?= t('This will be displayed next to the checkbox. If it is blank, the name of the attribute will be displayed.') ?></p>
    </div>
</fieldset>
