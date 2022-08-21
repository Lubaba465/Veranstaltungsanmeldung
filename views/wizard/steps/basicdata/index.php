<legend>
    <?= _('Allgemeine Informationen zur Veranstaltung') ?>
</legend>


<label for="wizard-coursetype" class="required">
    <?= _('Typ') ?>
</label>
<select name="coursetype" id="wizard-coursetype">
    <?php foreach ($types as $class => $subtypes) { ?>
        <optgroup label="<?= htmlReady($class) ?>">
            <?php foreach ($subtypes as $type) { ?>
                <option
                    value="<?= $type['id'] ?>"<?= $type['id'] == $values['coursetype'] ? ' selected="selected"' : '' ?>>
                    <?= htmlReady($type['name']) ?>
                </option>
            <?php } ?>
        </optgroup>
    <?php } ?>
</select>
</section>
<section>
    <label for="wizard-start-time" class="required">
        <?= _('Semester') ?>
    </label>
    <select name="start_time" id="wizard-start-time">
        <?php foreach (array_reverse($semesters) as $semester) { ?>
            <option
                value="<?= $semester->semester_id ?>"<?= $semester->semester_id == $values['start_time'] ? ' selected="selected"' : '' ?>>
                <?= htmlReady($semester->name) ?>
            </option>
        <?php } ?>
    </select>
</section>
<section>
    <label for="wizard-name" class="required">
        <?= _('Veranstaltungstitel') ?>
    </label>
    <input type="text" name="name" id="wizard-name" size="75" maxlength="254"
           value="<?= htmlReady($values['name']) ?>">
</section>
<section>
    <? $course_number_format_config = Config::get()->getMetadata('COURSE_NUMBER_FORMAT'); ?>
    <label for="wizard-number">
        <?= _('Veranstaltungsnummer') ?>
        <?= $course_number_format_config['comment'] ? tooltipIcon($course_number_format_config['comment']) : '' ?>
    </label>
    <? $course_number_format = Config::get()->COURSE_NUMBER_FORMAT; ?>
    <input type="text" name="number" id="wizard-number" size="20" maxlength="99"
           value="<?= htmlReady($values['number']) ?>"
           <? if ($course_number_format) : ?>pattern="<?= htmlReady($course_number_format) ?>" <? endif ?>/>
</section>


<section>
    <label for="wizard-description">
        <?= _('Beschreibung') ?>
    </label>
    <textarea name="description" id="wizard-description" cols="30"
              rows="1"><?= htmlReady($values['description']) ?></textarea>
</section>

<section>
    <label for="wizard-home-institute" class="required">
        <?= _('Heimat Einrichtung') ?>
    </label>
    <select name="institute" id="wizard-home-institute"
            data-ajax-url="<?= URLHelper::getLink('dispatch.php/course/wizard/ajax') ?>">
        <?php
        $fak_id = '';
        foreach ($institutes as $inst) :
            if ($inst['is_fak']) {
                $fak_id = $inst['Institut_id'];
            }
            ?>
            <option value="<?= $inst['Institut_id'] ?>"<?=
            $inst['Institut_id'] == $values['institute'] ? ' selected="selected"' : '' ?> class="<?=
            $inst['is_fak'] ? 'faculty' : ($inst['fakultaets_id'] == $fak_id ? 'sub_institute' : 'institute') ?>">
                <?= htmlReady($inst['Name']) ?>
            </option>
        <?php endforeach ?>
    </select>
    <?= Icon::create('arr_2right', 'sort')->asInput(["name" => 'select_institute', "value" => '1', "class" => 'hidden-js']) ?>
</section>

<section for="SWS_id_parameter">
    <div id="wizard-SWS">
        <label for="SWS_id_2" class="required">
            <?= _('SWS') ?>
        </label>
        <input id="VSWS0" class="sectionitem-6_3" step="0.5" min="0" type="number" name="VSWS" placeholder="SWS"
               value="<?= htmlReady($values['VSWS']) ?>">
    </div>

</section>

<?php if ($lsearch) : ?>

    <section>
        <label for="lecturer_id_2" class="required">
            <?= _('Lehrende') ?>
        </label>
        <div id="wizard-lecturersearch">
            <?= $lsearch ?>
        </div>
        <?php if ($values['lecturer_id_parameter']) : ?>
            <?= Icon::create('arr_2down', 'sort')->asInput(["name" => 'add_lecturer', "value" => '1']) ?>
        <?php endif ?>
    </section>
    <section>
        <div id="wizard-lecturers">


            <?php $i = 0;
            foreach ($values['lecturers'] as $id => $assigned) : ?>

                <?php if ($user = User::find($id)) : ?>
                    <?= $this->render_partial('basicdata/_userr',
                        ['class' => 'lecturer', 'inputname' => 'lecturers', 'user' => $user, 'i' => $i]);
                    $i++; ?>

                <?php endif ?>

            <?php endforeach ?>
        </div>
    </section>
<?php endif ?>
<section for="tutor_id_parameter">
    <label for="tutor_id_2">
        <?= _('Tutor/-in') ?>
    </label>
    <div id="wizard-tutorsearch">
        <?= $tsearch ?>
    </div>
    <?php if ($values['tutor_id_parameter']) : ?>
        <?= Icon::create('arr_2down', 'sort')->asInput(["name" => 'add_tutor', "value" => '1']) ?>
    <?php endif ?>
</section>

<section>
    <div id="wizard-tutors">

        <?php $j = 0;foreach ($values['tutors'] as $id => $assigned) : ?>
            <?php
            if ($user = User::find($id)) : ?>
                                <?php if (!in_array($id, array_keys($values['lecturers']))) : ?>

                <?= $this->render_partial('basicdata/_user',
    ['class' => 'tutor', 'inputname' => 'tutors', 'user' => $user, 'j' => $j]);   $j++;?>
            <?php endif ?>
            <?php endif ?>

        <?php endforeach ?>
    </div>
</section>



<section>
    <div class="HideA" id="sectionitem-4">
        <label>Lehrsprache</label>
        <select name="Sprache" size="1">
            <?php foreach ($TT_TYPES as $id => $val) { ?>
                <option value="<?= $id ?>"<?= $id == $values['Sprache'] ? ' selected="selected"' : '' ?>>
                    <?= htmlReady($val) ?>
                </option>         <?php } ?>  </select>
    </div>
</section>



<section for="max_id_parameter">
    <table>
        <tr>

            <td><label for="max_id_2"> <?= _('Max.Teilnehmerzahl:  ') ?></label></td>
            <td>
                <div id="wizard-teilnehmer">
                    <input id="Teilnehmer0" name="Teilnehmer"  min="0" placeholder="0" value="<?= htmlReady($values['Teilnehmer']) ?>"
                           type="number"></div>
            </td>
        </tr>


    </table>
</section>



<section for="anzahl_id_parameter">

    <table>
        <tr>

            <td><label for="anzahl_id_2"> <?= _('Anzahl der Gruppen:   ') ?></label></td>
            <td>
                <div id="wizard-anzahl">
                    <input id="AGruppe0" name="AGruppe"   min="0"placeholder="0"type="number" value="<?= htmlReady($values['AGruppe']) ?>">
                </div>
            </td>
        </tr>


    </table>
</section>
