<legend>
    <?= _("zugehörigkeit zu folgende Module") ?>
</legend>

<section for="Model_id_parameter">
    <label for="tutor_id_2" class="required">
        <?= _('Module') ?>
    </label>
    <div id="wizard-Modelsearch">
        <?= $dsearch ?>
    </div>
    <?php if ($values['Model_id_parameter']) : ?>
        <?= Icon::create('arr_2down', 'sort')->asInput(["name" => 'add_model', "value" => '1']) ?>
    <?php endif ?>
</section>
<section>
    <div id="wizard-models">
        <div class="description<?= count($values['models']) ? '' : ' hidden-js' ?>">
            <?= _('bereits zugeordnet:') ?>
        </div>
        <?php foreach ($values['models'] as $id => $assigned) : ?>
            <?php if ($model = Modul::find($id)) : ?>
                <?= $this->render_partial('modeldata/_model',
                    ['class' => 'model', 'inputname' => 'models', 'model' => $model]) ?>
            <?php endif ?>
        <?php endforeach ?>
    </div>
</section>



<section for="generale_id_parameter">
    <label for="gener_id_2">
        <?= _('Studium Generale:') ?>
    </label>
    <div id="wizard-gener">

        <input type="number" name="SGenerale"  value="<?= htmlReady($values['SGenerale']) ?>" placeholder="0"> Plätze
    </div>
</section>


<section for="erergier_id_parameter">

    <div id="wizard-ener_2">
        <input name="Ener" type="checkbox" value="1"<?= htmlReady($values["Ener"]) ? " checked" : "" ?>> Energierelevant

    </div>
</section>


<section for="nach_id_parameter">
    <div id="wizard-nach">

        <input name="Nach" type="checkbox" value="1"<?= htmlReady($values["Nach"]) ? " checked" : "" ?>> Bezug zur Nachhaltigkeit
    </div>
</section>

