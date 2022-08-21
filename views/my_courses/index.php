<?php if (count($courses) < 1) : ?>
    <?php echo MessageBox::info(
        'Es wurden keine Veranstaltungen gefunden.') ?>
<?php else : ?>
    <table class="dates default sortable-table">
        <?php if (!$GLOBALS['perm']->have_perm('admin')) : ?>
            <caption>

                <? if (!($semester->name)) : ?>
                    <?= _('Meine Angemeldeten Veranstaltungen') ?>
                <? else : ?>
                    <?= htmlReady(sprintf(_('Meine Angemeldeten Veranstaltungen im %s'), $semester->name)) ?>
                <? endif ?>        </caption>
        <? elseif ($GLOBALS['perm']->have_perm('admin')) : ?>
            <caption>

                <? if (!($semester->name)) : ?>
                    <?= _('Angemeldete Veranstaltungen') ?>
                <? else : ?>
                    <?= htmlReady(sprintf(_('Angemeldete Veranstaltungen im %s'), $semester->name)) ?>
                <? endif ?>        </caption>
        <?php endif ?>
            <colgroup class="hidden-small-down">
                <col>
                <col>
                <col>
                <col>
                <col>
                <col>
                <col width="150">
            </colgroup>
            <thead>
            <tr>
                <th data-sort="htmldata"><?= _('Nr.') ?></th>
                <th data-sort="text" class="hidden-small-down"><?= _('Titel') ?></th>
                <th data-sort="text" class="hidden-small-down"><?= _('VA-Type') ?></th>
                <th data-sort="htmldata"><?= _('Semester') ?></th>
                <th><?php echo 'Beschreibung' ?></th>
                <th data-sort="num"><?= _('TN ') ?></th>

                <th><?php echo 'Status ' ?></th>
                <th><?php echo 'Ansprechpartner' ?></th>
                <th><?php echo 'Aktionen' ?></th>
            </tr>
            </thead>
        <tbody>

        <?php

        $result = [1];

        foreach (SemType::getTypes() as $inst) {


            $result[] = [
                'label' => $inst['name'],
            ];


        } ?>
        <?php foreach (    $courses

        as $course) : ?>

            <tr>
                <td data-sort-value="<?= htmlReady($course['VNummer']) ?>" ><?= htmlReady($course['VNummer']) ?></td>






            <td><a data-dialog="buttons=false" href=" <?= $controller->url_for('my_courses/detail/' . $course['id']) ?>">
                    <img alt="info-circle" title="Veranstaltungsdetails anzeigen" style="cursor: pointer" width="16"
                         height="16"
                         src="http://localhost/studip-4.6.1/4.6/public/assets/images/icons/grey/info-circle.svg"
                         class="icon-role-inactive icon-shape-info-circle"> </a>
                <?= htmlReady($course['Titel']) ?>
            </td>
            <td>
                <?php

                $statement = DBManager::get()->prepare(
                    "SELECT name FROM sem_types WHERE id=" . $course['sem_types_id'] . ""
                );
                $types = $statement->execute();
                $types = $statement->fetchColumn();

                echo($types) ?>  </td>
            <td>
                <?php

                $statement = DBManager::get()->prepare(
                    "SELECT name FROM semester_data WHERE semester_id=" . $course['Semester_ID'] . ""
                );
                $types = $statement->execute();
                $types = $statement->fetchColumn();

                echo($types) ?>  </td>
            <td>
                <?= htmlReady($course['description']) ?>
            </td>

            <td>

                <?= htmlReady($course['Teilnehmer']) ?>

            </td>
                <?php if ($GLOBALS['perm']->have_perm('admin')) {?>

                    <td>
                        <?php
                        $status = "";
                        switch ($course['astatus']) {
                            case "fertig":
                                $status = "abgeschlossen";
                                break;
                            case "noch nicht bearbeitet":
                                $status = "noch nicht bearbeitet";
                                break;
                            case "in Bearbeitung":
                                $status = "in Bearbeitung";
                                break;
                        } ?>

                        <?= htmlReady(   $status ) ?>

                    </td>
              <?php  } else{?>

            <td>
                    <?php
                    $status = "";
                    switch ($course['status']) {
                        case "fertig":
                            $status = "abgeschlossen";
                            break;
                        case "noch nicht bearbeitet":
                            $status = "noch nicht bearbeitet";
                            break;
                        case "in Bearbeitung":
                            $status = "in Bearbeitung";
                            break;
                    } ?>

                <?= htmlReady(   $status ) ?>

            </td><?}?>
            <?php
            $statement = DBManager::get()->prepare(
                "SELECT user_id FROM VAPlannung WHERE id='" . $course['id'] . "'");
            $types = $statement->execute();

            ?>


            <td>

                <?php $zeile = $statement->fetchColumn();
                $statementt = DBManager::get()->prepare(
                    "SELECT  CONCAT(Vorname, ' ', Nachname, '') as name FROM auth_user_md5 WHERE user_id='" . $zeile . "'");
                $typess = $statementt->execute();

                $typess = $statementt->fetchColumn();
                echo $typess . "<br>";


                ?>


            <td>


                <?php

                if ($GLOBALS['perm']->have_perm('admin')) {
                    if ($course['status'] == 'in Bearbeitung' && $course['astatus'] == 'noch nicht bearbeitet') {
                        $actions = ActionMenu::get();
                        $actions->addLink(
                            $controller->url_for('my_courses/edit/' . $course['id']),
                            'Veranstaltung Bearbeiten',
                            Icon::create('edit')
                        );
                        $actions->addLink(
                            $controller->url_for('my_courses/Freigaben/' . $course['id']),
                            'Veranstaltung zurückgaben',
                            Icon::create('share'),
                            ['data-confirm' => _('Möchten Sie wirklich die Veranstaltung Freigeben?')]

                        );
                        $actions->addLink(
                            $controller->url_for('wizardA/copy/' . $course['id']),
                            _('Veranstaltung überführen'),
                            Icon::create(
                                'seminar',
                                Icon::ROLE_CLICKABLE)
                        );


                        $actions->addLink(
                            $controller->url_for('my_courses/delete/' . $course['id']),
                            'Veranstaltung Löschen',
                            Icon::create('trash'),
                            ['data-confirm' => _('Möchten Sie wirklich die Veranstaltung löschen?')]
                        );

                        echo $actions->render();
                    } elseif ($course['status'] == 'fertig') {
                    }
                } elseif ($GLOBALS['perm']->have_perm('dozent')) {
                    $actions = ActionMenu::get();

                    if ($course['status'] == 'noch nicht bearbeitet'
                    ) {

                        $actions->addLink(
                            $controller->url_for('my_courses/Freigaben/' . $course['id']),
                            'Veranstaltung Freigaben',
                            Icon::create('share'),
                            ['data-confirm' => _('Möchten Sie wirklich die Veranstaltung Freigeben?')]

                        );

                        $actions->addLink(
                            $controller->url_for('my_courses/edit/' . $course['id']),
                            'Veranstaltung Bearbeiten',
                            Icon::create('edit')
                        ); $actions->addLink(
                            $controller->url_for('wizard/copy/' . $course['id']),
                            'Veranstaltung kopiern',
                            Icon::create('files')

                        );
                        $actions->addLink(
                            $controller->url_for('my_courses/delete/' . $course['id']),
                            'Veranstaltung Löschen',
                            Icon::create('trash'),
                            ['data-confirm' => _('Möchten Sie wirklich die Veranstaltung löschen?')]
                        );
                        echo $actions->render();
                    } else
                    {
                        $actions->addLink(
                            $controller->url_for('wizard/copy/' . $course['id']),
                            'Veranstaltung kopiern',
                            Icon::create('files')

                        );


                        echo $actions->render();

                }}


                ?>

            </td>
        </tr>

            <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>
