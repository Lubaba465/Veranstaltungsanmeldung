<? if ($content) : ?>
    <form class="default course-wizard-step-<?= $stepnumber ?>" action="<?= $controller->url_for('wizardA/process', $stepnumber, $temp_id) ?>" method="post" data-secure>
        <fieldset>
        <?= $content ?>
        </fieldset>

        <footer data-dialog-button>
            <input type="hidden" name="step" value="<?= $stepnumber ?>">
        <? if (!$first_step): ?>
            <?= Studip\Button::create(
                _('Zurück'),
                'back',
                $dialog ? ['data-dialog' => 'size=50%'] : []
            ) ?>
        <? endif; ?>
            <?= Studip\Button::create(
                _('Weiter'),
                'next',
                $dialog ? ['data-dialog' => 'size=50%'] : []
            ) ?>
        </footer>
    </form>
<? else : ?>
    <?= Studip\LinkButton::createCancel(
        _('Zurück zu meiner Veranstaltungsübersicht'),
        $controller->url_for(''),
        ['data-dialog-button' => '']
    ) ?>
<? endif ?>
