<?php

class WizardController extends AuthenticatedController
{
    /**
     * @var Array steps the wizard has to execute in order to create a new course.
     */
    public $steps = [];

    public function before_filter(&$action, &$args)
    {
        Navigation::activateItem("/browse/veranstaltungsanmeldung");

        $this->plugin = $this->dispatcher->current_plugin;


        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        $this->flash = Trails_Flash::instance();

        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/Veranstaltungsanmeldung.css');
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/veranstaltungsanmeldung.js');


        PageLayout::setTitle("Neue Veranstaltung anlegen");

        $this->me = $GLOBALS['user'];

        $this->steps = array(
            array(
                "name" => "Studiendaten",
                "classname" => "BasicDataSte"
            ), array(
                "name" => "Studiendaten",
                "classname" => "ModelDataStep"
            ), array(
                "name" => "Studiendaten",
                "classname" => "AdvancedDataStep"
            )







        );
    }

    /**
     * Just some sort of placeholder for initial calling without a step number.
     */
    public function index_action()
    {


        $this->redirect('wizard/step/0');
    }

    /**
     * Fetches the wizard step with the given number and gets the
     * corresponding template.
     *
     * @param int $number step number to show
     * @param String $temp_id temporary ID for the course to create
     */
    public function step_action($number = 0, $temp_id = '')
    {
        $step = $this->getStep($number);
        if (!$temp_id) {
            $this->initialize();
        } else {
            $this->temp_id = $temp_id;
        }
        if ($number == 0) {
            $this->first_step = true;
        }

        $this->values = $this->getValues();
        $this->content = $step->getStepTemplate($this->values, $number, $this->temp_id);
        $this->stepnumber = $number;

    }

    /**
     * Processes a finished wizard step by saving the gathered values to
     * session.
     * @param int $step_number the step we are at.
     * @param String $temp_id temporary ID for the course to create
     */
    public function process_action($step_number, $temp_id)
    {
        $this->temp_id = $temp_id;
// Get request data and store it in session.
        $iterator = Request::getInstance()->getIterator();
        $values = [];
        while ($iterator->valid()) {
            $values[$iterator->key()] = $iterator->current();
            $iterator->next();
        }
        if ($this->steps[$step_number]['classname']) {
            $this->setStepValues($this->steps[$step_number]['classname'], $values);
        }
// Back or forward button clicked -> set next step accordingly.
        if (Request::submitted('back')) {
            $next_step = $this->getNextRequiredStep($step_number, 'down');
        } else if (Request::submitted('next')) {
// Validate given data.
            if ($this->getStep($step_number)->validate($this->getValues())) {
                $next_step = $this->getNextRequiredStep($step_number, 'up');
                /*
                * Validation failed -> stay on current step. Error messages are
                * provided via the called step class validation method.
                */
            } else {
                $next_step = $step_number;
            }
// The "create" button was clicked -> create course.
        } else if (Request::submitted('create')) {
            $_SESSION['coursewizard'][$this->temp_id]['copy_basic_data'] = Request::submitted('copy_basic_data');
            if ($this->getValues()) {
           if ($this->course = $this->createCourse()) {
// A studygroup has been created.
                        /*if (in_array($this->course->status, studygroup_sem_types() ?: [])) {
                            $message = MessageBox::success(
                                sprintf(_('Die Studien-/Arbeitsgruppe "%s" wurde angelegt. ' .
                                    'Sie können sie direkt hier weiter verwalten.'),
                                    htmlReady($this->course->name)));
                            $target = $this->url_for('studygroup/edit/?cid=' . $this->course->id);
// "Normal" course.
                        } else {*/
                            if (Request::int('dialog')) {
                                $message = MessageBox::success(
                                    ('Die Veranstaltung wurde angelegt.'));
                            } else {
                                $message = MessageBox::success(
                                   ('Die Veranstaltung  wurde angelegt. Sie können sie direkt hier weiter verwalten.'));
                                         }

                        PageLayout::postMessage($message);
                        $this->redirect('my_courses');
                    } else {
                        PageLayout::postMessage(MessageBox::error(
                            _('Die Veranstaltung konnte nicht angelegt werden.')));
                        $this->redirect('wizard');
                    }
                }
             else {
                PageLayout::postMessage(MessageBox::error(_('Die angegebene Veranstaltung wurde bereits angelegt.')));
                $this->redirect('wizard');
            }

            $stop = true;
            /*
            * Something other than "back", "next" or "create" was clicked,
            * e.g. QuickSearch
            * -> stay on current step and process given values.
            */
        } else {
            $stepclass = $this->steps[$step_number]['classname'];
            $result = $this->getStep($step_number)
                ->alterValues($this->getValues());
            $_SESSION['coursewizard'][$temp_id][$stepclass] = $result;
            $next_step = $step_number;
        }
        if (!$stop) {
// We are after the last step -> all done, show summary.
            if ($next_step >= sizeof($this->steps)) {
                $this->redirect($this->url_for('wizard/summary', $next_step, $temp_id));
// Redirect to next step.
            } else {
                $this->redirect($this->url_for('wizard/step', $next_step, $this->temp_id));
            }
        }
    }

    /**
     * We are after last step: all set and ready to create a new course.
     */
    public function summary_action($stepnumber, $temp_id)
    {
        $this->stepnumber = $stepnumber;
        $this->temp_id = $temp_id;
        if (!$this->getValues()) {
            throw new UnexpectedValueException('no data found');
        }
        if (isset($_SESSION['coursewizard'][$this->temp_id]['source_id'])) {
            $this->source_course = Course::find($_SESSION['coursewizard'][$this->temp_id]['source_id']);
        }
    }

    /**
     * Wrapper for ajax calls to step classes. Three things must be given
     * via Request:
     * - step number
     * - method to call in target step
     * - parameters for the target method (will be passed in given order)
     */
    public function ajax_action()
    {
        $stepNumber = Request::int('step');
        $method = Request::get('method');
        $parameters = Request::getArray('parameter');
        $result = call_user_func_array([$this->getStep($stepNumber), $method], $parameters);
        if (is_array($result) || is_object($result)) {
            $this->render_json($result);
        } else {
            $this->render_text($result);
        }

    }

    public function forward_action($step_number, $temp_id)
    {
        $this->temp_id = $temp_id;
        $stepclass = $this->steps[$step_number]['classname'];
        $result = $this->getStep($step_number)->alterValues($this->getValues() ?: []);
        $this->setStepValues($stepclass, $result);
        $this->redirect($this->url_for('wizard/step', $step_number, $this->temp_id));
    }

    /**
     * Copy an existing course.
     */
    public function copy_action($id)
    {

        $course = Veranstaltungsanmelden::find($id);
        $values = [];
        for ($i = 0; $i < sizeof($this->steps); $i++) {
            $step = $this->getStep($i);
            $values = $step->copy($course, $values);
        }
        $values['source_id'] = $course->id;
        $this->initialize();
        $_SESSION['coursewizard'][$this->temp_id] = $values;
        $this->redirect($this->url_for('wizard/step/0/' . $this->temp_id, ['cid' => '']));
  }

    /**
     * Creates a temporary ID for storing the wizard values in session.
     */
    private function initialize()
    {
        $temp_id = md5(uniqid(microtime()));
        $_SESSION['coursewizard'][$temp_id] = [];
        $this->temp_id = $temp_id;
    }

    /**
     * Wizard finished: we can create the course now. First store an empty,
     * invisible course for getting an ID. Then, iterate through steps and
     * set values from each step.
     * @param bool $cleanup cleanup session after course creation?
     * @return Veranstaltungsanmelden
     * @throws Exception
     */
    private function createCourse($cleanup = true)
    {

        foreach (array_keys($this->steps) as $n) {
            $step = $this->getStep($n);
            if ($step->isRequired($this->getValues())) {
                if (!$step->validate($this->getValues())) {
                    unset($_SESSION['coursewizard'][$this->temp_id]);
                    return false;
                }
            }
        }
// Create a new (empty) course so that we get an ID.
        $course = new Veranstaltungsanmelden();
        $course->setId($course->getNewId());


        // Each (required) step stores its own values at the course object.
        for ($i = 0; $i < sizeof($this->steps); $i++) {
            $step = $this->getStep($i);
            if ($step->isRequired($this->getValues())) {
                if ($stored = $step->storeValues($course, $this->getValues())) {
                    $course = $stored;
                } else {
                    $course = false;
                    unset($_SESSION['coursewizard'][$this->temp_id]);
                    break;
//throw new Exception(_('Die Daten aus Schritt ' . $i . ' konnten nicht gespeichert werden, breche ab.'));
                }
            }
        }
// Cleanup session data if necessary.
        if ($cleanup) {
            unset($_SESSION['coursewizard'][$this->temp_id]);
        }
        return $course;
    }

    /**
     * Fetches the class belonging to the wizard step at the given index.
     * @param $number
     * @return mixed
     */
    private function getStep($number)
    {
        $classname = $this->steps[$number]['classname'];
        return new $classname();
    }

    /**
     * Not all steps are required for each course type, some sem_classes must
     * not have study areas, for example. So we need to check which step is
     * required next, starting from an index and going up or down, according
     * to navigation through the wizard.
     * @param $number
     * @param string $direction
     * @return mixed
     */
    private function getNextRequiredStep($number, $direction = 'up')
    {
        $found = false;
        switch ($direction) {
            case 'up':
                $i = $number + 1;
                while (!$found && $i < sizeof($this->steps)) {
                    $step = $this->getStep($i);
                    if ($step->isRequired($this->getValues())) {
                        $found = true;
                    } else {
                        $i++;
                    }
                }
                break;
            case 'down':
                $i = $number - 1;
                while (!$found && $i >= 0) {
                    $step = $this->getStep($i);
                    if ($step->isRequired($this->getValues())) {
                        $found = true;
                    } else {
                        $i--;
                    }
                }
                break;
        }
        return $i;
    }

    /**
     * Gets values stored in session for a given step, or all
     * @param string $classname the step to get values for, or all
     * @return Array
     */
    private function getValues($classname = '')
    {
        if ($classname) {
            return $_SESSION['coursewizard'][$this->temp_id][$classname] ?: [];
        } else {
            return $_SESSION['coursewizard'][$this->temp_id] ?: [];
        }
    }

    /**
     * @param $stepclass class name of the current step.
     * @return Array
     */
    private function setStepValues($stepclass, $values)
    {
        $_SESSION['coursewizard'][$this->temp_id][$stepclass] = $values;
    }

}
