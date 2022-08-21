<div class="model">
    <input type="hidden" name="models[<?= $model->id ?>]" value="1" id="<?= $model->id ?>"/>
    <?= htmlReady($model->getDisplayName()) ?>
    <?= Icon::create('trash', 'clickable')->asInput(["name" => 'remove_model['.$model->id.']', "onclick" => "return STUDIP.ThesisTopics.removePerson('".$model->id."')"]) ?>
</div>
