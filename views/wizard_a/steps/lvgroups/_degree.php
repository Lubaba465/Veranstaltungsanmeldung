<div class="degree">
    <input type="hidden" name="degrees[<?= $degree->id ?>]" value="1" id="<?= $degree->id ?>"/>
    <?= htmlReady($degree->getDisplayName()) ?>
    <?= Icon::create('trash', 'clickable')->asInput(["name" => 'remove_degree['.$degree->id.']', "onclick" => "return STUDIP.ThesisTopics.removePerson('".$degree->id."')"]) ?>
</div>
