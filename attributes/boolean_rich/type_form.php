<?php

use Concrete\Core\Editor\CkeditorEditor;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Attribute\View $view
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Package\BooleanRich\Attribute\BooleanRich\Controller $controller
 * @var Concrete\Core\Editor\EditorInterface|Concrete\Core\Editor\CkeditorEditor $editor
 * @var int $maxLabelLength
 * @var bool|null $akCheckedByDefault
 * @var string $akCheckboxLabel
 * @var int $akCheckboxLabelLength
 * @var string $coreVersion
 * @var Concrete\Core\Validation\CSRF\Token $token
 */

$akCheckedByDefault = !empty($akCheckedByDefault);

$editorCreator = static function() use ($editor, $akCheckboxLabel, $akCheckboxLabelLength, $maxLabelLength) {
    if ($editor instanceof CkeditorEditor && method_exists($editor, 'outputEditorWithOptions')) {
        $html = $editor->outputEditorWithOptions(
            'akCheckboxLabel',
            [
                'enterMode' => 2, // CKEDITOR.ENTER_BR
            ],
            $akCheckboxLabel
        );
    } else {
        $html = $editor->outputStandardEditor('akCheckboxLabel', $akCheckboxLabel);
    }
    $html .= <<<EOT
<div class="small text-muted text-right text-end">
    <span id="akCheckboxLabelLength">{$akCheckboxLabelLength}</span>/{$maxLabelLength}
</div>
EOT
    ;

    return $html;
};

if (version_compare($coreVersion, '9') < 0) {
    ?>
    <fieldset>
        <legend><?= t('Checkbox Options') ?></legend>
        <div class="form-group">
            <label class="control-label"><?= t('Default Value') ?></label>
            <div class="checkbox">
                <label>
                    <?= $form->checkbox('akCheckedByDefault', 1, $akCheckedByDefault) ?>
                    <?= t('The checkbox will be checked by default.') ?>
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label"><?= t('Label') ?></label>
            <?= $editorCreator() ?>
            <p class="help-block"><?= t('This will be displayed next to the checkbox. If it is blank, the name of the attribute will be displayed.') ?></p>
        </div>
    </fieldset>
    <?php
} else {
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
            <?= $editorCreator() ?>
            <p class="help-block"><?= t('This will be displayed next to the checkbox. If it is blank, the name of the attribute will be displayed.') ?></p>
        </div>
    </fieldset>
    <?php
}
?>
<script>
(function() {

let element, editor, hooked = false, currentLabelLength, updateTimer = null;

function initialize(){
    element = document.querySelector('[name=akCheckboxLabel]');
    if (!element) {
        return;
    }
    <?php
    if ($editor instanceof CkeditorEditor) {
        ?>
        if (!window.CKEDITOR) {
            setTimeout(initialize, 100);
            return;
        }
        editor = CKEDITOR.instances[element.id];
        if (!editor) {
            CKEDITOR.on('instanceReady', initialize);
            return;
        }
        editor.on('change', () => startUpdateCounter());
        editor.on('mode', function() {
            if (this.mode === 'source') {
                var editable = editor.editable();
                editable.attachListener(editable, 'input', () => startUpdateCounter());
            }
        });
        <?php
    }
    ?>
    element.addEventListener('input', () => startUpdateCounter());
    updateCounter();
};

const ALREADY_SEEN = <?= json_encode(['' => 0, $akCheckboxLabel => $akCheckboxLabelLength])?>;

function startUpdateCounter() {
    clearTimeout(updateTimer);
    updateTimer = setTimeout(() => updateCounter(), 200);
}

async function updateCounter() {
    clearTimeout(updateTimer);
    updateTimer = null;
    const label = editor ? editor.getData() : element.value;
    if (ALREADY_SEEN.hasOwnProperty(label)) {
        if (ALREADY_SEEN.hasOwnProperty(label) !== null) {
            displayCounter(ALREADY_SEEN[label]);
        }
        return;
    }
    ALREADY_SEEN[label] = null;
    const response = await window.fetch(
        <?= json_encode((string) $view->action('checkLength')) ?>,
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            },
            body: <?= json_encode(rawurlencode($token::DEFAULT_TOKEN_NAME) . '=' . rawurlencode($token->generate('boolean_rich_check_length')) . '&label=') ?> + window.encodeURIComponent(label),
        }
    );
    const labelLength = await response.json();
    if (typeof labelLength !== 'number') {
        console.error(labelLength);
        return;
    }
    ALREADY_SEEN[label] = labelLength;
    displayCounter(labelLength);
}

function displayCounter(len) {
    if (currentLabelLength === len) {
        return;
    }
    currentLabelLength = len;
    document.querySelector('#akCheckboxLabelLength').innerHTML = len.toString();
    if (currentLabelLength > <?= $maxLabelLength ?> && !hooked) {
        for (let el = element; el = el.parentElement; el && !hooked) {
            if (el.tagName === 'FORM') {
                el.addEventListener('submit', (e) => {
                    if (currentLabelLength <= <?= $maxLabelLength ?>) {
                        return;
                    }
                    e.preventDefault();
                    const message = <?= json_encode(t('The label of the checkbox is too long: please shorten it.')) ?>;
                    if (window?.ConcreteAlert?.error) {
                        window.ConcreteAlert.error({message});
                    } else {
                        window.alert(message);
                    }
                });
                hooked = true;
            }
        }
    }
}

if (document.readyState === 'complete') {
    console.log(1);
    initialize();
} else {
    console.log(2);
    document.addEventListener('DOMContentLoaded', initialize);
}

})();
</script>
