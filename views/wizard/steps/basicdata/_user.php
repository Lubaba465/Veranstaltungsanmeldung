

<div class="<?= $class ?>">
    <input type="hidden" name="<?= $inputname ?>[<?= $user->id ?>]" value="1" id="<?= $user->id ?>"/>

    <table>
        <thead>
        <tr>
            <th>
                <div style="
                    width: 150px;">  <?= htmlReady($user->getFullname('full_rev')) ?></div>
            </th>

            <th><input id="<?= $user->id ?>" type="number" name='L<? echo $j ?>SWS' step="0.5" min="0"
                       value="<?= htmlReady($values["L" . $j . "SWS"]) ?>" placeholder="SWS/LVS">LVS
            </th>

            <th>    <?= Icon::create('trash', 'clickable')->asInput(["name" => 'remove_' . $class . '[' . $user->id . ']', "onclick" => "return STUDIP.CourseWizard.removePerson('" . $user->id . "')"]) ?>
            </th>
        </tr>
        </thead>
    </table>

</div>

