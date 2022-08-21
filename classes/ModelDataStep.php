<?php
/**
 * LVGroupsWizardStep.php
 * Course wizard step for assigning LV Groups.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


class ModelDataStep implements CourseWizardStep
{
    /**
     * Returns the Flexi template for entering the necessary values
     * for this step.
     *
     * @param Array $values Pre-set values
     * @param int $stepnumber which number has the current step in the wizard?
     * @param String $temp_id temporary ID for wizard workflow
     * @return String a Flexi template for getting needed data.
     */
    public function getStepTemplate($values, $stepnumber, $temp_id)
    {
        $tt_path = '/Lubaba/Veranstaltungsanmeldung/views/wizard/steps';
        $factory = new Flexi_TemplateFactory($GLOBALS['PLUGINS_PATH'] . $tt_path);
        $tpl = $factory->open('modeldata/index');

        if ($this->setupTemplateAttributes($tpl, $values, $stepnumber, $temp_id)) {
            return $tpl->render();
        }
    }

    protected function setupTemplateAttributes($tpl, $values, $stepnumber, $temp_id)
    {
        // We only need our own stored values here.
        $values = $values[__CLASS__];

        if ($values['model_id_parameter']) {
            Request::getInstance()->offsetSet('model_id_parameter', $values['model_id_parameter']);
        }
        if ($values['model_id']) {
            Request::getInstance()->offsetSet('model_id', $values['model_id']);
        }


        $models ="SELECT modul_id, CONCAT(mmd.bezeichnung, ' (', code, ')') as name FROM mvv_modul mm
                    LEFT JOIN mvv_modul_deskriptor mmd USING(modul_id)
                    WHERE (code LIKE :input OR mmd.bezeichnung LIKE :input)
    ORDER BY name ASC";
        $model_search = new SQLSearch($models, 'Studiengänge suchen', 'username');

        $tpl->set_attribute('search_model', QuickSearch::get('studiengang_id', $model_search)
            ->fireJSFunctionOnSelect('STUDIP.ThesisTopics.add_model')
            ->withButton()
            ->render());


        // Quicksearch for lecturers.
        // No JS: Keep search value and results for displaying in search select box.
        if ($values['lecturer_id']) {
            Request::getInstance()->offsetSet('lecturer_id', $values['lecturer_id']);
        }
        if ($values['lecturer_id_parameter']) {
            Request::getInstance()->offsetSet('lecturer_id_parameter', $values['lecturer_id_parameter']);
        }



        if (!$values['lecturers'] && $GLOBALS['perm']->have_perm('dozent') && !$GLOBALS['perm']->have_perm('admin')) {
            $values['lecturers'][$GLOBALS['user']->id] = true;
        }
        // Add lecturer from my courses filter.
        if ($GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER && !$values['lecturers'] && Request::isXhr()) {
            $values['lecturers'][$GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER] = true;
        }
        if (!$values['lecturers']) {
            $values['lecturers'] = [];
        }
        if (!$values['models']) {
            $values['models'] = [];
        }

        list($lsearch, $dsearch)  = array_values($this->getSearch($values['coursetype'],
            array_keys($values['lecturers']), array_keys($values['models'])));

        $tpl->set_attribute('lsearch', $lsearch);
        $tpl->set_attribute('dsearch', $dsearch);
        $tpl->set_attribute('values', $values);

        return $tpl;
    }

    /**
     * Catch form submits other than "previous" and "next" and handle the
     * given values. This is only important for no-JS situations.
     * @param Array $values currently set values for the wizard.
     * @return bool
     */
    public function alterValues($values)
    {
        // We only need our own stored values here.
        $values = $values[__CLASS__];
        // Add a participating institute.

        // Add a lecturer.
        if (Request::submitted('add_lecturer') && Request::option('lecturer_id')) {
            $values['lecturers'][Request::option('lecturer_id')] = true;
            unset($values['lecturer_id']);
            unset($values['lecturer_id_parameter']);
        }
        // Remove a lecturer.
        if ($remove = array_keys(Request::getArray('remove_lecturer'))) {
            $remove = $remove[0];
            unset($values['lecturers'][$remove]);
        }

        // Add a model.
        if (Request::submitted('add_model') && Request::option('studiengang_id')) {
            $values['models'][Request::option('studiengang_id')] = true;
            unset($values['studiengang_id']);
            unset($values['model_id_parameter']);
        }
        // Remove a model.
        if ($remove = array_keys(Request::getArray('remove_model'))) {
            $remove = $remove[0];
            unset($values['models'][$remove]);
        }
        return $values;
    }

    /**
     * Validates if given values are sufficient for completing the current
     * course wizard step and switch to another one. If not, all errors are
     * collected and shown via PageLayout::postMessage.
     *
     * @param mixed $values Array of stored values
     * @return bool Everything ok?
     */
    public function validate($values)
    {
        // We only need our own stored values here.
        $values = $values[__CLASS__];
        $ok = true;
        $errors = [];
        if (!$values['models']) {
            $errors[] = _('Bitte geben Sie mindestens ein Modul ein.');
        }
        if ($errors) {
            $ok = false;
            PageLayout::postError(_('Bitte beheben Sie erst folgende Fehler, bevor Sie fortfahren:'), $errors);
        }
        return $ok;
    }

    /**
     * Stores the given values to the given course.
     *
     * @param Veranstaltungsanmelden $course the course to store values for
     * @param Array $values values to set
     * @return Veranstaltungsanmelden The course object with updated values.
     */
    public function storeValues($course, $values)
    {
        if (@$values['copy_basic_data'] === true) {
            $source = Veranstaltungsanmelden::find($values['source_id']);
        }
        $values = $values[__CLASS__];
        $tcourse_id = $course->id;

        if (isset($source)) {
            $course->setData($source->toArray('untertitel ort sonstiges art teilnehmer vorrausetzungen lernorga leistungsnachweis ects admission_turnout modules'));
            foreach ($source->datafields as $one) {
                $df = $one->getTypedDatafield();
                if ($df->isEditable()) {
                    $course->datafields->findOneBy('datafield_id', $one->datafield_id)->content = $one->content;
                }
            }
        }



        if ($values['models']) {
            foreach ($values['models'] as $id => $assigned) {


                $modul_id = $id;
                $tmodul = Module::findByModul($tcourse_id, $modul_id);
                if (!$tmodul){
                    $tmodul = new Module();
                    $tmodul->id = $tcourse_id;
                    $tmodul->modul_ID = $modul_id;

                    if ($tmodul->store()) {
                        StudipLog::log('TT_CREATE', $course->id, null, 'course mit Assistent angelegt');
                    } else {
                        PageLayout::postMessage(MessageBox::error(
                            _('modul Save Error')));
                        return null;
                    }
                }
            }
            }
        $course->Nachhaltigkeit=$values['Nach'];
        $course->Energierelevant=$values['Ener'];
        $course->SGenerale=$values['SGenerale'];

        if ($course->store()) {
            StudipLog::log('SEM_CREATE', $course->id, null, 'course - GeneralData mit Assistent angelegt');
            return $course;
        } else {
            return null;
        }
    }
    /**
     * Checks if the current step needs to be executed according
     * to already given values. A good example are study areas which
     * are only needed for certain sem_classes.
     *
     * @param Array $values values specified from previous steps
     * @return bool Is the current step required for a new course?
     */
    public function isRequired($values)
    {
        return true;
    }

    /**
     * Copy values for basic data wizard step from given course
     * So far not needed, the interface requires its implementation
     * @param Veranstaltungsanmelden $course
     * @param Array $values
     */
    public function copy($course, $values)
    {
        {

        $data = [
            'Nach' => $course->Nachhaltigkeit,
            'Ener' => $course->Energierelevant,
            'SGenerale' => $course->SGenerale,

        ];
            $models = $course->modul->pluck('modul_ID');
            $data['models'] = array_flip($models);

        $values[__CLASS__] = $data;
        return $values;
    }}

    /**
     * Fetches the default deputies for a given person if the necessary
     * config options are set.
     * @param $user_id user whose default deputies to get
     * @return Array Default deputy user_ids.
     */
    public function getDefaultDeputies($user_id)
    {
        if (Config::get()->DEPUTIES_ENABLE && Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE) {
            $deputies = getDeputies($user_id, 'full_rev_username');
            $result = [];
            foreach ($deputies as $d) {
                $result[] = [
                    'id' => $d['user_id'],
                    'name' => $d['fullname']
                ];
            }
            return $result;
        } else {
            return [];
        }
    }

    public function getSearch($course_type, $institute_ids, $exclude_lecturers = [],$exclude_tutors = [])
    {
        $search = 'user';
        $psearch = new PermissionSearch($search,
            sprintf(_("Ansprechpartner hinzufügen")),
            'user_id',
            __CLASS__ . '::lsearchHelper'
        );
        $lsearch = QuickSearch::get('lecturer_id', $psearch)
            ->withButton(['search_button_name' => 'search_lecturer', 'reset_button_name' => 'reset_lsearch'])
            ->fireJSFunctionOnSelect('STUDIP.CourseWizard.addLecturer')
            ->render();

        $sql_models = "SELECT modul_id, CONCAT(mmd.bezeichnung, ' (', code, ')') as name FROM mvv_modul mm
                    LEFT JOIN mvv_modul_deskriptor mmd USING(modul_id)
                    WHERE (code LIKE :input OR mmd.bezeichnung LIKE :input) and stat= 'genehmigt'
    ORDER BY name ASC";
        $model_ssearch = new SQLSearch($sql_models, 'Module hinzufügen', 'Arbeitsgruppe_id');

        $dsearch = QuickSearch::get('studiengang_id', $model_ssearch)
            ->withButton(['search_button_name' => 'search_model', 'reset_button_name' => 'reset_tsearch'])
            ->fireJSFunctionOnSelect('STUDIP.Veranstaltungsanmeldung.addModel')
            ->render();



        return compact('lsearch', 'dsearch');
    }

    public static function lsearchHelper($psearch, $context)
    {
        $ret['permission'] = 'dozent';
        $ret['exclude_user'] = array_keys((array)$context['lecturers']);
        $ret['institute'] = array_merge([$context['institute']], array_keys((array)$context['participating']));
        return $ret;
    }

}
