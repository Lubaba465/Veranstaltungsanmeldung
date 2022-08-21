<legend>
    <?= _('Raum- und Zeitwünsche') ?>
</legend>
<?php
$T_SEMESTERS = array(
    '1 Semester' => "1 Semester", '2 Semester' => "2 Semester"
); ?>
<section for="dauer_id_parameter">

    <label> <?= _('Dauer:') ?></label>
    <div id="wizard-dauer">

        <select name="Dauer" size="1">
            <?php foreach ($T_SEMESTERS as $ids => $vals) { ?>
                <option value="<?= $ids ?>"<?= $ids == $values['Dauer'] ? ' selected="selected"' : '' ?>>
                    <?= htmlReady($vals) ?>
                </option>         <?php } ?> </select></div>
</section>

<?php
$TT_Turnus = array(
    'blockveranstaltung' => "Blockveranstaltung", 'wöchentlich' => "Wöchentlich", 'zweiwöchentlich' => "Zwei Wöchentlich", 'nach vereinbarung' => "Nach vereinbarung"
); ?>
<section for="turnus_id_parameter">
    <label for="turnus_id_2"> <?= _('Turnus der Veranstaltunszeiten:') ?></label>

    <select name="Turnus" size="1">
        <?php foreach ($TT_Turnus as $id => $val) { ?>
            <option value="<?= $id ?>"<?= $id == $values['Turnus'] ? ' selected="selected"' : '' ?>>
                <?= htmlReady($val) ?>
            </option>         <?php } ?>  </select>
</section>


<section class="col-2">
    <label for="start-date">
        <?= _('Wunsch- und Ausschusstermine:') ?>
    </label>
       <textarea name="WTermin" cols="30" rows="2"><?= htmlReady($values['WTermin']) ?></textarea>


</section>
<section for="raum_id_parameter">
    <label for="raum_id_2">
        Raumwünsche:
    </label>
    <div id="wizard-raumW">


        <textarea name="WRaum" cols="30" rows="1"><?= htmlReady($values['WRaum']) ?></textarea>
    </div>
</section>

<section>
    <label for="wizard-Zugangberechtigung">
        <?= _('Angaben zur Zugangsberechtigung (Fristen, Termine, Anzahl, Warteliste):') ?>
    </label>
    <textarea name="angaben" id="wizard-angaben" cols="30"
              rows="1"><?= htmlReady($values['angaben']) ?></textarea>
</section>


<label for="raumA_id_2"> <?= _('  Sonstiges(Raumausstattung..weitere Wünsche):') ?></label>
<div id="wizard-raumA">

    <textarea name="RAus" cols="30" rows="1"><?= htmlReady($values['RAus']) ?></textarea>
</div>
</section>


