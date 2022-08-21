<?php

class MyCoursesController extends AuthenticatedController
{


    static protected $widgets = null;

    public function before_filter(&$action, &$args)
    {
        if (!$GLOBALS['perm']->have_perm('dozent')) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));
    }


    public function index_action()
    {

        Navigation::activateItem("/browse/veranstaltungsanmeldung");


        if ($GLOBALS['perm']->have_perm("admin")) {
            $this->courses = Veranstaltungsanmelden::findAll();
            $filter = Request::get('filter');
            $_SESSION['veranstaltung_search']['selects']['institute'] = Request::get('institute');
            $_SESSION['veranstaltung_search']['selects']['semester'] = Request::get('semester');
            $_SESSION['veranstaltung_search']['selects']['status'] = Request::get('status');
            $_SESSION['veranstaltung_search']['selects']['astatus'] = Request::get('astatus');

            if ($filter) {
                $this->courses = $this->findAllByFilters();
            } else {
                unset($_SESSION['veranstaltung_search']['selects']);
                $this->courses = $this->findAllInCourse();
            }
            PageLayout::setTitle('Veranstaltungsanmeldung');

        } else if ($GLOBALS['perm']->have_perm("dozent")) {


            $statement = DBManager::get()->prepare(
                "SELECT max(semester_id) FROM semester_data "
            );
            $current = $statement->execute();
            $current = $statement->fetchColumn();

            $this->courses = Veranstaltungsanmelden::findBySQL("user_id = ? AND status='noch nicht bearbeitet' AND Semester_ID='" . $current . "' ", [$GLOBALS['user']->id]);
            $filter = Request::get('filter');
            $_SESSION['veranstaltung_search']['selects']['institute'] = Request::get('institute');
            $_SESSION['veranstaltung_search']['selects']['semester'] = Request::get('semester');
            $_SESSION['veranstaltung_search']['selects']['status'] = Request::get('status');
            $_SESSION['veranstaltung_search']['selects']['astatus'] = Request::get('astatus');

            if (!$filter) {
                $this->courses = $this->findAllInCourse();
            }
            if ($filter) {

                $this->courses = $this->findAllByFilters();
            }

            PageLayout::setTitle('Veranstaltungsanmeldung');

        }


        $this->set_sidebar_parameters();

    }


    /**
     * Sets the parameters for the Sidebar.
     *
     * @return void
     */
    function set_sidebar_parameters()
    {
        $sidebar = Sidebar::get();


        $semesterId = Request::option('semester', 'all') ?:
            (UserConfig::get($this->me->id)->SELECTED_SEMESTER ?: 'all');
        $this->semester = $semesterId === 'current' ? Semester::findCurrent() : Semester::find($semesterId);


        $Semester = $sidebar->addWidget(new OptionsWidget(_('Semester auswählen')));
        $Semester->addSelect(
            _('Semester'),
            $this->getURLHelper('semester'),
            'semester',
            $this->getSemester(),
            $_SESSION['veranstaltung_search']['selects']['semester']
        );
        if ($GLOBALS['perm']->have_perm("admin")) {


            $ASTATUS = array(
                "noch nicht bearbeitet" => "noch nicht bearbeitet",
                "fertig" => "abgeschlossen",
                "in Bearbeitung" => "in Bearbeitung"
            );
            $STATUS_type = $sidebar->addWidget(new OptionsWidget(_('Status')));
            $STATUS_type->addSelect(
                _('Status'),
                $this->getURLHelper('status'),
                'astatus',
                $ASTATUS,
                $_SESSION['veranstaltung_search']['selects']['astatus']
            );
        } else {
            $STATUS = array(
                "all" => "-- Alle Status --",
                "fertig" => "abgeschlossen",
                "noch nicht bearbeitet" => "noch nicht bearbeitet",
                "in Bearbeitung" => "in Bearbeitung",
            );
            $STATUS_type = $sidebar->addWidget(new OptionsWidget(_('Status')));
            $STATUS_type->addSelect(
                _('Status'),
                $this->getURLHelper('status'),
                'status',
                $STATUS,
                $_SESSION['veranstaltung_search']['selects']['status']
            );
        }
        $institute = $sidebar->addWidget(new OptionsWidget(_('Fakultätsbereich')));
        $institute->addSelect(
            _('Einrichtung'),

            $this->getURLHelper('institute'),
            'institute',
            $this->getInstitutes(),
            $_SESSION['veranstaltung_search']['selects']['institute']
        );
        if (!$GLOBALS['perm']->have_perm("admin")) {
            {
                $actions = new ActionsWidget();

                $actions->addLink(
                    _("Neue Veranstaltung anmelden"),
                    $this->link_for('wizard'),
                    Icon::create("seminar+add", "clickable"));

                $sidebar->addWidget($actions);
            }
        }
    }

    private function getSemester()
    {


        $Semesters = [];
        $Semesters['current'] = _('Aktuelle Semester');

        $semesters = Semester::getAll();

        foreach ($semesters as $semester) {
            $Semesters[$semester['id']] = $semester['name'];
        }
        return $Semesters;
    }

    private function getURLHelper($param, $value = null)
    {

        $params = array(
            'semester' => $_SESSION['veranstaltung_search']['selects']['semester'],
            'status' => $_SESSION['veranstaltung_search']['selects']['status'],
            'astatus' => $_SESSION['veranstaltung_search']['selects']['astatus'],
            'institute' => $_SESSION['veranstaltung_search']['selects']['institute'],
            'filter' => true

        );

        $url = URLHelper::getURL('plugins.php/veranstaltungsanmeldung/my_courses', $params);
        return $url;
    }

    /**
     * Get institutes for the institute-select-filter (dropdown).
     * The institute filter shows all available institutes and presents the 2-level hierarchy with indented names.
     *
     * @return array with key => value pairs like: array('institute_id' => 'institute_name')
     */
    private function getInstitutes()
    {
        $institutes = [];
        $institutes['all'] = _('-- Alle Einrichtungen --');

        $insts = Institute::getInstitutes();
        foreach ($insts as $institute) {
            $institutes[$institute['Institut_id']] = ($institute['is_fak'] ? '' : '  ') . $institute['Name'];
        }
        return $institutes;
    }


    /**
     *
     * @param string $id
     */
    public function delete_action($id)
    {
        $course = new Veranstaltungsanmelden($id);
        $tt_title = $course->Titel;

        $course->delete();
        PageLayout::postSuccess(sprintf(_('Die Veranstaltung "%s" wurde gelöscht.'), $tt_title));

        $this->redirect('my_courses');
    }


    /**
     * Completely delete a course from the system.
     *
     * @param string $id of course to delete
     */
    public function Freigaben_action($id)
    {
        $course = new Veranstaltungsanmelden($id);
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $course->status = 'in Bearbeitung';
            $course->astatus='noch nicht Bearbeitet';

        } else {
            $course->astatus='in Bearbeitung';

            $course->status = 'noch nicht Bearbeitet';
        }
        $course->store();


        $this->redirect('my_courses');
    }


    /**
     * Update a course
     *
     * @param string $id  of course to update
     */
    public function edit_action($id)
    {
        Navigation::activateItem('/browse/veranstaltungsanmeldung');
        $this->id = Request::option('cid', $id);

        $this->course = Veranstaltungsanmelden::find($this->id);

        PageLayout::setTitle(_("Veranstaltung verwalten"));

        $this->setupInputFields($this->course);
    }


    /**
     * Update a is course
     *
     *
     * @param string $id of course to update
     */
    public function detail_action($id)
    {
        Navigation::activateItem('/browse/veranstaltungsanmeldung');
        $this->id = Request::option('cid', $id);

        $this->course = Veranstaltungsanmelden::find($this->id);

        PageLayout::setTitle(_("Veranstaltung verwalten"));

        $this->setupInputFields($this->course);

    }


    /**
     * Update a is course
     *
     * @param string $id of course
     * to update
     */
    public function set_action($id)
    {
        $course = new Veranstaltungsanmelden($id);
        $tmodul= new Module();
        $this->msg = [];
        $tmodul->id = $course->id;

        $changemade = false;

        $this->setupInputFields($course);
        foreach (array_merge($this->attributes, $this->institutional, $this->descriptions) as $field) {
            $req_value = Request::get($field['name']);


            switch ($field['name']) {

                case 'course_status':
                    if ($this->verifyFieldChanges($course->sem_types_id, $req_value)) {
                        $course->sem_types_id = $req_value;
                        $changemade = true;
                    }
                    break;

                case 'title':
                    if ($this->verifyFieldChanges($course->Titel, $req_value)) {
                        $course->Titel = $req_value;
                        $changemade = true;
                    }
                    break;
                case 'nummer':
                    if ($this->verifyFieldChanges($course->VNummer, $req_value)) {
                        $course->VNummer = $req_value;
                        $changemade = true;
                    }
                    break;
                case 'description':
                    if ($this->verifyFieldChanges($course->description, $req_value)) {
                        $course->description = $req_value;
                        $changemade = true;
                    }
                    break;
                case 'sws':
                    if ($this->verifyFieldChanges($course->SWS, $req_value)) {
                        $course->SWS = $req_value;
                        $changemade = true;
                    }
                    break;
                case 'teilnehmendenzahl':
                    if ($this->verifyFieldChanges($course->Teilnehmer, $req_value)) {
                        $course->Teilnehmer = $req_value;
                        $changemade = true;
                    }
                    break;
                case 'Lehrsprache':
                    if ($this->verifyFieldChanges($course->Lehrsprache, $req_value)) {
                        $course->Lehrsprache = $req_value;
                        $changemade = true;
                    }
                    break;
                case 'dauer':
                    if ($this->verifyFieldChanges($course->Dauer, $req_value)) {
                        $course->Dauer = $req_value;
                        $changemade = true;
                    }
                    break;


                case 'turnus':
                    if ($this->verifyFieldChanges($course->Turnus, $req_value)) {
                        $course->Turnus = $req_value;
                        $changemade = true;
                    }
                    break;


                case 'raumwunsch':
                    if ($this->verifyFieldChanges($course->Wunschraum, $req_value)) {
                        $course->Wunschraum = $req_value;
                        $changemade = true;
                    }
                    break;

                case 'ausstattung':
                    if ($this->verifyFieldChanges($course->Ausstattung, $req_value)) {
                        $course->Ausstattung = $req_value;
                        $changemade = true;
                    }
                    break;
                case 'AngZugang':
                    if ($this->verifyFieldChanges($course->AngZugang, $req_value)) {
                        $course->AngZugang = $req_value;
                        $changemade = true;
                    }
                    break;
                case 'anzahl':
                    if ($this->verifyFieldChanges($course->Anzahl, $req_value)) {
                        $course->Anzahl = $req_value;
                        $changemade = true;
                    }
                    break;

                case 'SGenerale':
                    if ($this->verifyFieldChanges($course->SGenerale, $req_value)) {
                        $course->SGenerale = $req_value;
                        $changemade = true;
                    }
                    break;

                case 'Nachhaltigkeit':
                    if ($this->verifyFieldChanges($course->Nachhaltigkeit, $req_value)) {
                        $course->Nachhaltigkeit = $req_value;
                        $changemade = true;
                    }
                    break;

                case 'Energierelevant':
                    if ($this->verifyFieldChanges($course->Energierelevant, $req_value)) {
                        $course->Energierelevant = $req_value;
                        $changemade = true;
                    }
                    break;
            }


        }



        if ($changemade){
            $course->chdate = time();
        }

        $tmod = Module::findByModul($id, $tmodul->modul_id);



        $success =  $course->store();



        if ($success === false) {
            PageLayout::postError($this->_('Beim Speichern der Veranstaltung ist ein Fehler aufgetreten.'));
        } else {
            // store relations

            $module = [];
            foreach (Request::getArray('module') as $modul_id) {
                $module[$modul_id] = true;
            }
            Module::storeNewAndDelete($id,$module);


            PageLayout::postSuccess(sprintf(_('Die Daten der Veranstaltung "%s" wurden verändert.')
                , $course->Titel));}
        $this->flash['msg'] = $this->msg;
        $this->flash['open'] = Request::get("open");
        $this->redirect('my_courses');
    }


    private function verifyFieldChanges($old, $new)
    {
        if ($old !== $new) {
            return true;
        } else {
            return false;
        }
    }


    /**
     *
     * @param Veranstaltungsanmelden $course
     */
    private function setupInputFields($course)
    {
        $this->mkstring = $course->mkdate ? date('d.m.Y, H:i', $course->mkdate) : _('unbekannt');
        $this->chstring = $course->chdate ? date('d.m.Y, H:i', $course->mkdate) : _('unbekannt');

        $TT_DEGREE_TYPES = array(
            'blockveranstaltung' => "Blockveranstaltung", 'wöchentlich' => "Wöchentlich", 'zweiwöchentlich' => "Zwei Wöchentlich", 'nach vereinbarung' => "Nach vereinbarung"
        );

        $T_DEGREE_TYPES = array(
            '1 Semester' => "1 Semester", '2 Semester' => "2 Semester"
        );

        $lehrsprache = array(
            'deutsch' => "Deutsch", 'english' => "English", 'nach absprech mit den studierenden' => "Nach absprech mit den studierenden"
        );

        $this->attributes = [];


        $sem = SemType::getTypes();

        $this->attributes[] = [
            'title' => _('Typ der Veranstaltung'),
            'name' => 'course_status',
            'must' => true,
            'type' => 'nested-select',
            'value' => $course->sem_types_id,
            'choices' => $this->_getTypes($sem)
        ];



        $this->attributes[] = [
            'title' => _('Titel'),
            'name' => 'title',
            'must' => true,
            'type' => 'text',
            'i18n' => false,
            'value' => $course->Titel
        ];


        $this->attributes[] = [
            'title' => _('Veranstaltungsnummer'),
            'name' => 'nummer',
            'must' => false,
            'type' => 'text',
            'i18n' => false,
            'value' => $course->VNummer,
        ];



        $this->attributes[] = [
            'title' => _('Beschreibung'),
            'name' => 'description',
            'must' => true,
            'type' => 'textarea',
            'i18n' => false,
            'value' => $course->description,
        ];
        $this->attributes[] = [
            'title' => _('Lehrsprache'),
            'name' => 'Lehrsprache',
            'must' => false,
            'type' => 'select',
            'value' => $course->Lehrsprache,
            'choices' => $lehrsprache
        ];



        $this->attributes[] = [
            'title' => _('SWS'),
            'name' => 'sws',
            'must' => true,
            'type' => 'number',
            'i18n' => false,
            'value' => $course->SWS,
        ];

        $this->attributes[] = [
            'title' => _("max. Teilnehmendenzahl"),
            'name' => "teilnehmendenzahl",
            'must' => false,
            'type' => 'number',
            'value' => $course->Teilnehmer,
        ];
        $this->attributes[] = [
            'title' => _("Anzahl den Gruppem"),
            'name' => "anzahl",
            'must' => false,
            'type' => 'number',
            'value' => $course->Anzahl,
        ];
        $this->institutional = [];

        $this->institutional[] = [
            'title' => _('Module:'),
            'name' => 'module[]',
            'type' => 'nested-select',
            'value' => $this->getMyModule($this->id),
            'choices' => $this->getModule(),
            'multiple' => true,
        ];
        $this->institutional[] = [
            'title' => _('Studium Generale:'),
            'name' => 'SGenerale',
            'type' => 'number',
            'value' => $course->SGenerale,

        ];



        $this->institutional[] = [
            'title' => _('Energierelevant:'),
            'name' => 'Energierelevant',
            'type'      => 'checkbox',
            'must'      => false,
            'value'     => 1,
            'checked' => $course->Energierelevant
        ];
        $this->institutional[] = [
        'title' => _('Bezug zur Nachhaltigkeit:'),
        'name' => 'Nachhaltigkeit',
        'type'      => 'checkbox',
        'must'      => false,
        'value'     => 1,
        'checked' => $course->Nachhaltigkeit
        ];

        $this->descriptions = [];

        $this->descriptions[] = [
            'title' => _('Dauer'),
            'name' => 'dauer',
            'must' => false,
            'type' => 'select',
            'value' => $course->Dauer,
            'choices' => $T_DEGREE_TYPES
        ];


        $this->descriptions[] = [
            'title' => _('Turnus'),
            'name' => 'turnus',
            'must' => false,
            'type' => 'select',
            'value' => $course->Turnus,
            'choices' => $TT_DEGREE_TYPES
        ];
        $this->descriptions[] = [
            'title' => _("Angaben zur Zugangsberechtigung (Fristen, Termine, Anzahl, Warteliste):"),
            'name' => "AngZugang",
            'type' => 'textarea',
            'i18n' => false,
            'value' => $course->AngZugang

        ];

        $this->descriptions[] = [
            'title' => _("        Raumwünsche:
"),
            'name' => "raumwunsch",
            'type' => 'textarea',
            'i18n' => false,
            'value' => $course->Wunschraum

        ];


        $this->descriptions[] = [
            'title' => _("Raumausstattung"),
            'name' => "ausstattung",
            'type' => 'textarea',
            'i18n' => false,
            'value' => $course->Ausstattung

        ];



    }

    private function getMyModule($id)
    {
        $myModule = [];
        $myModuleObj = Module::findAllByModuleAndStudiengang($id);

        foreach ($myModuleObj as $myModulObj) {
            $myModule[] = $myModulObj->modul_ID;
        }
        return $myModule;
    }

    private function getModule()
    {
        $sql_div = "SELECT modul_id, CONCAT(mmd.bezeichnung, ' (', code, ')') as name FROM mvv_modul mm
                    LEFT JOIN mvv_modul_deskriptor mmd USING(modul_id) where stat= 'genehmigt'
    ORDER BY name ASC";
        $moduleObj = DBManager::get()->query($sql_div)->fetchAll(PDO::FETCH_ASSOC);
        $module = [];
        foreach ($moduleObj as $modulObj) {
            $module[$modulObj['modul_id']] = [
                'label' => $modulObj['name'],
                'children' => [],
            ];
        }
        return $module;
    }

    private function _getTypes($institutes)
    {

        $result = [1];

        foreach ($institutes as $inst) {


            $result[] = [
                'label' => $inst['name'],
            ];
        }
        return $result;


    }


    /**
     *
     *
     * @return array
     */
    private function findAllByFilters()
    {
        $institute = $_SESSION['veranstaltung_search']['selects']['institute'];
        $semester = $_SESSION['veranstaltung_search']['selects']['semester'];
        $status = $_SESSION['veranstaltung_search']['selects']['status'];
        $astatus = $_SESSION['veranstaltung_search']['selects']['astatus'];

        if ($GLOBALS['perm']->have_perm("admin")) {
            $current = Semester::findCurrent();
            $status='in Bearbeitung';
            $sql = "SELECT *
                 FROM VAPlannung tt
                         WHERE

            astatus = '" . $astatus . "'
 AND tt.institut_id = IF(IFNULL('" . $institute . "','')='' OR '" . $institute . "' = 'all', tt.institut_id , '" . $institute . "')
                      AND
                tt.Semester_ID = IF(IFNULL('" . $semester . "','')='' OR '" . $semester . "' = 'current','" . $current['semester_id'] . "' , '" . $semester . "')

                      ";
        } else if ($GLOBALS['perm']->have_perm("dozent")) {
            $current = Semester::findCurrent();
            $user_id = $GLOBALS['user']->id;
            $sql = "SELECT *
                FROM VAPlannung tt
                        WHERE

              user_id   = '" . $user_id . "'
                                           AND status = IF(IFNULL('" . $status . "','')='' OR '" . $status . "' = 'all', status , '" . $status . "')
 AND tt.institut_id = IF(IFNULL('" . $institute . "','')='' OR '" . $institute . "' = 'all', tt.institut_id , '" . $institute . "')
                     AND
                tt.Semester_ID = IF(IFNULL('" . $semester . "','')='' OR '" . $semester . "' = 'current','" . $current['semester_id'] . "' , '" . $semester . "')

                     ";

        }
        $courses = DBManager::get()->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return     $courses;
    }

    /*/**
     *
     *
     * @return array
     */
    private function findAllInCourse()
    {
        $institute = $_SESSION['veranstaltung_search']['selects']['institute'];
        $semester = $_SESSION['veranstaltung_search']['selects']['semester'];
        $status = $_SESSION['veranstaltung_search']['selects']['status'];
        $astatus = $_SESSION['veranstaltung_search']['selects']['astatus'];

        if ($GLOBALS['perm']->have_perm("admin")) {
            $astatus='noch nicht bearbeitet';
            $status='in Bearbeitung';
            $sql = "SELECT *
                       FROM VAPlannung
                           WHERE
                       astatus =  '" . $astatus . "' AND  status =  '" . $status . "'And institut_id = IF(IFNULL('" . $institute . "','')='' OR '" . $institute . "' = 'all', institut_id , '" . $institute . "')
                      ";
        } else if ($GLOBALS['perm']->have_perm("dozent")) {
            $user_id = $GLOBALS['user']->id;

            $current = Semester::findCurrent();
            $sql = "SELECT *
                    FROM VAPlannung
                        WHERE

           user_id   = '" . $user_id . "'    AND
                           status = IF(IFNULL('" . $status . "','')='' OR '" . $status . "' = 'all', status , '" . $status . "')
AND  institut_id = IF(IFNULL('" . $institute . "','')='' OR '" . $institute . "' = 'all', institut_id , '" . $institute . "')
         AND   Semester_ID='" . $current['semester_id'] . "'
      ";
        }
        $courses = DBManager::get()->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return     $courses;

    }


}









