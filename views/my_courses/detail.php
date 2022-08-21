<style>
    #tablefix {
        padding: 0;
    }

    #tablefix > header {
        margin: 0px;
    }

    #tablefix table {
        margin-bottom: 0;
        border-bottom: 0;
    }

    #tablefix table tbody tr:last-child td {
        border-bottom: 0;
    }
</style>

<article class="studip" id="tablefix">
    <header>
        <h1><?= _('Allgemeine Informationen') ?></h1>
    </header>
    <table class="default">
        <colgroup>
            <col width="40%">
        </colgroup>
        <tbody>
        <? if ($course->Titel) : ?>
            <tr>
                <td><strong><?= _('Untertitel') ?></strong></td>
                <td><?= htmlReady($course->Titel) ?></td>
            </tr>
        <? endif ?>

        <? if ($course->VNummer) : ?>
            <tr>
                <td><strong><?= _('Veranstaltungsnummer') ?></strong></td>
                <td><?= htmlReady($course->VNummer) ?></td>
            </tr>
        <? endif ?>
        <? if ($course->Semester_ID): ?>
            <tr>

                <?php $statement = DBManager::get()->prepare(
                    "SELECT name FROM semester_data WHERE semester_id=" . $course['Semester_ID'] . ""
                );
                $types = $statement->execute();
                $types = $statement->fetchColumn();

                ?>
                <td>
                    <strong><?= _('Semester') ?></strong>
                </td>
                <td>
                    <? echo($types) ?>
                </td>
            </tr>
        <? endif ?>

        <? if ($course->SWS): ?>
            <tr>


                <td>
                    <strong><?= _('SWS') ?></strong>
                </td>
                <td><?= htmlReady($course->SWS) ?></td>

            </tr>
        <? endif ?>
        <? if ($course->Teilnehmer): ?>
            <tr>


                <td>
                    <strong><?= _('erwartete Teilnehmeranzahl') ?></strong>
                </td>
                <td><?= htmlReady($course->Teilnehmer) ?></td>

            </tr>

            <? if ($course->Anzahl) : ?>
                <tr>
                    <td><strong><?= _('Anzahl der Gruppen;') ?></strong></td>
                    <td><?= htmlReady($course->Anzahl) ?></td>
                </tr>
            <? endif ?>
            <? if ($course->description) : ?>
                <tr>
                    <td><strong><?= _('Beschreibung') ?></strong></td>
                    <td><?= htmlReady($course->description) ?></td>
                </tr>
            <? endif ?>
        <? endif ?>
        <? if ($course->Institut_id): ?>
            <tr>


                <td>
                    <strong><?= _('Heimat-Einrichtung') ?></strong>
                </td>
                <td><?php

                    $statemen = DBManager::get()->prepare(
                        "SELECT Name FROM Institute WHERE Institut_id='$course->Institut_id'"
                    );
                    $type = $statemen->execute();
                    $type = $statemen->fetchColumn();

                    echo($type) ?></td>
            </tr>
        <? endif ?>
        <? if ($course->sem_types_id): ?>
            <tr>

            <? if ($course->Lehrsprache) : ?>
                <tr>
                    <td><strong><?= _('Sprache') ?></strong></td>
                    <td><?= htmlReady($course->Lehrsprache) ?></td>
                </tr>
            <? endif ?>
                <td>
                    <strong><?= _('Veranstaltungstyp') ?></strong>
                </td>
                <td><?php

                    $statement = DBManager::get()->prepare(
                        "SELECT name FROM sem_types WHERE id=" . $course['sem_types_id'] . ""
                    );
                    $types = $statement->execute();
                    $types = $statement->fetchColumn();

                    echo($types) ?></td>

            </tr>
        <? endif ?>
        <? if ($course->Dauer): ?>
            <tr>


                <td>
                    <strong><?= _('Dauer') ?></strong>
                </td>
                <td><?= htmlReady($course->Dauer) ?></td>

            </tr>
        <? endif ?>
        <? if ($course->Turnus): ?>
            <tr>


                <td>
                    <strong><?= _('Turnus') ?></strong>
                </td>
                <td><?= htmlReady($course->Turnus) ?></td>

            </tr>
        <? endif ?>

        </tbody>
    </table>
</article>

<article class="studip" id="tablefix">
    <header>
        <h1><?= _('Lehrende') ?></h1>
    </header>
    <table class="default">
        <colgroup>
            <col width="40%">
        </colgroup>
        <tbody>

        <? if ($course->id): ?>
            <tr>


                <?php
                $statement = DBManager::get()->prepare(
                    "SELECT user_id FROM veranstalter WHERE id='" . $course['id'] . "'");
                $zeil = $statement->execute();

                ?>
                <td>
                    <strong><?= _('Lehrende') ?></strong>
                </td>

                <td>


                    <?php while ($zeil = $statement->fetchColumn()) {
                        $statementt = DBManager::get()->prepare(
                            "SELECT  CONCAT(Vorname, ' ', Nachname, '') as name FROM auth_user_md5 WHERE user_id='" . $zeil . "'");
                        $typess = $statementt->execute();

                        $typess = $statementt->fetchColumn();
                        echo $typess . "<p>LVS:";

                        $statementt = DBManager::get()->prepare(
                            "SELECT  LVS FROM veranstalter WHERE user_id='" . $zeil . "'");
                        $typess = $statementt->execute();

                        $typess = $statementt->fetchColumn();
                        echo $typess . "<br>";

                    } ?>

                </td>
            </tr>
        <? endif ?>

        </tbody>
    </table>
</article>

<article class="studip" id="tablefix">
    <header>
        <h1><?= _('Tutor') ?></h1>
    </header>
    <table class="default">
        <colgroup>
            <col width="40%">
        </colgroup>
        <tbody>

        <? if ($course->id): ?>
            <tr>


                <?php
                $statement = DBManager::get()->prepare(
                    "SELECT user_id  FROM tutor WHERE id='" . $course['id'] . "'");
                $zeile= $statement->execute();

                ?>
                <td>
                    <strong><?= _('Tutor') ?></strong>
                </td>

                <td>


                    <?php while ($zeile = $statement->fetchColumn()) {
                        $statementt = DBManager::get()->prepare(
                            "SELECT  CONCAT(Vorname, ' ', Nachname, '')  as name FROM auth_user_md5 WHERE user_id='" . $zeile . "'");
                        $tutors = $statementt->execute();

                        $tutors = $statementt->fetchColumn();
                        echo $tutors . "<p>LVS:";


                        $statementt = DBManager::get()->prepare(
                            "SELECT  LVS FROM tutor WHERE user_id='" . $zeile . "'");
                        $tutors = $statementt->execute();

                        $tutors = $statementt->fetchColumn();
                        echo $tutors . "<br>";


                    } ?>


                </td>
            </tr>
        <? endif ?>

        </tbody>
    </table>
</article>

<article class="studip" id="tablefix">
    <header>
        <h1><?= _('Zugehörigkeit zu folgende Module') ?></h1>
    </header>
    <table class="default">
        <colgroup>
            <col width="40%">
        </colgroup>
        <tbody>

        <? if ($course->id): ?>
            <tr>



                <td>
                    <strong><?= _('Zugehörigkeit zu folgende Module') ?></strong>
                </td>

                <td>


                    <?php

                    $statement =DBManager::get()->prepare(
                        "SELECT modul_ID FROM vermod WHERE id='".$course['id']."'");
                   $statement->execute();
                    while ($types = $statement->fetchColumn()){
                        $statementt = DBManager::get()->prepare(
                            "SELECT CONCAT( code , ' ', mmd.bezeichnung, '') as name FROM mvv_modul mm
                    LEFT JOIN mvv_modul_deskriptor mmd USING(modul_id)
                    WHERE modul_id='" . $types. "'");
                        $typess = $statementt->execute();

                        $typess = $statementt->fetchColumn();
                        echo $typess . "<br>";

                    } ?>




            <? if ($course->SGenerale) : ?>
                <tr>
                    <td><strong><?= _('SGenerale:') ?></strong></td>
                    <td>
                        <?= htmlReady($course-> SGenerale)?>
          </td>
                </tr>
            <? endif ?>
            <? if ($course->Nachhaltigkeit) : ?>
                <tr>
                    <td><strong><?= _('Nachhaltigkeit:') ?></strong></td>
                    <td><?if ($course->Nachhaltigkeit=="1")
                        {echo('ja');}  ?></td>
                </tr>
            <? endif ?>
            <? if ($course->Energierelevant) : ?>
                <tr>
                    <td><strong><?= _('Energierelevant:') ?></strong></td>
                    <td><?if ($course->Energierelevant=="1")
                        {echo('ja');}  ?></td>
                </tr>
            <? endif ?>




                </td>
            </tr>
        <? endif ?>

        </tbody>
    </table>
</article>

<article class="studip" id="tablefix">
    <header>
        <h1><?= _('Raum_ und Zeitwünsche') ?></h1>
    </header>
    <table class="default">
        <colgroup>
            <col width="40%">
        </colgroup>
        <tbody>
        <? if ($course->AngZugang) : ?>
            <tr>
                <td><strong><?= _('Angeben zur Zugangsberechtigung (Fristen, Termine, Anzahl, Warteliste):') ?></strong></td>
                <td><?= htmlReady($course->AngZugang) ?></td>
            </tr>
        <? endif ?>
        <? if ($course->start_date) : ?>
            <tr>
                <td><strong><?= _('Zeitwunsch:') ?></strong></td>
                <td><?= htmlReady($course->start_date) ?></td>
            </tr>
        <? endif ?>
        <? if ($course->Wunschraum) : ?>
            <tr>
                <td><strong><?= _('Raum Wünsche') ?></strong></td>
                <td><?= htmlReady($course->Wunschraum) ?></td>
            </tr>
        <? endif ?>
        <? if ($course->Ausstattung) : ?>
            <tr>
                <td><strong><?= _('Weitere Wünsche:') ?></strong></td>
                <td><?= htmlReady($course->Ausstattung) ?></td>
            </tr>
        <? endif ?>

        </tbody>
    </table>
</article>
