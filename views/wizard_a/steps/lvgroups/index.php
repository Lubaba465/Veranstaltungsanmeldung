<legend>
    <?= _('Lehrveranstaltungsgruppen') ?>
</legend>

<div>
    <h2>
        <span >
            <?= _('zugehörigkeit zu folgende Module( von Lehrende Zugeordnet wurde):') ?>
        </span>
    </h2>

    <td>
<div class="modul">


        <input type="hidden" name="number" id="wizard-number"  value="<?= htmlReady($values['number']) ?>"

    <?php



    $value= htmlReady($values['number']);
   $statement =DBManager::get()->prepare(
            "SELECT modul_ID FROM vermod WHERE id='$value'");
        $statement->execute();
        while ($types = $statement->fetchColumn()){
            $statementt = DBManager::get()->prepare(
                "SELECT CONCAT( code , ' ', mmd.bezeichnung, '') as name FROM mvv_modul mm
                    LEFT JOIN mvv_modul_deskriptor mmd USING(modul_id)
                    WHERE modul_id='" . $types. "'");
            $typess = $statementt->execute();

            $typess = $statementt->fetchColumn();
            echo "<br>". $typess ;

        }

    ?>

        </div>

</div>

<div id="assigned" data-ajax-url="<?= $ajax_url ?>" data-forward-url="<?= $no_js_url ?>">
    <h2>
        <span class="required">
            <?= _('Bereits zugewiesen') ?>
        </span>
    </h2>
    <ul class="css-tree">
        <li class="lvgroup-tree-assigned-root keep-node" data-id="root">
            <ul id="lvgroup-tree-assigned-selected">
              <? foreach ($selection->getAreas() as $area) : ?>
                    <?= $this->render_partial('lvgroups/lvgroup_entry', compact('area')) ?>
              <? endforeach; ?>
            </ul>
        </li>
    </ul>
</div>
<? if (!$values['locked']) : ?>

	<div id="lvgroup-tree-open-nodes">
	<? foreach ($open_lvg_nodes as $opennode) : ?>
		<input type="hidden" name="open_lvg_nodes[]" value="<?= $opennode; ?>">
	<? endforeach; ?>
	</div>

    <div id="studyareas" data-ajax-url="<?= $ajax_url ?>"
        data-forward-url="<?= $no_js_url ?>" data-no-search-result="<?=_('Es wurde kein Suchergebnis gefunden.') ?>">
        <h2><?= _('Lehrveranstaltungsgruppen Suche') ?></h2>
        <div>
            <input type="text" size="40" style="width: auto;" name="search" id="lvgroup-tree-search"
                   value="<?= $values['searchterm'] ?>">
            <span id="lvgroup-tree-search-start">
                <?= Icon::create('search', 'clickable')->asInput(["name" => 'start_search', "onclick" => "return STUDIP.MVV.CourseWizard.searchTree()", "class" => $search_result?'hidden-no-js':'']) ?>
            </span>
            <span id="lvgroup-tree-search-reset" class="hidden-js">
                <?= Icon::create('refresh', 'clickable')->asInput(["name" => 'reset_search', "onclick" => "return STUDIP.MVV.CourseWizard.resetSearch()", "class" => $search_result?'':' hidden-no-js']) ?>
            </span>
        </div>

        <div id="lvgsearchresults" style="display: none;">
            <h2><?= _('Suchergebnisse') ?></h2>
            <ul class="collapsable css-tree">

            </ul>
        </div>
        <h2><?= _('Alle Lehrveranstaltungsgruppen') ?></h2>
        <ul class="collapsable css-tree">
            <li class="lvgroup-tree-root tree-loaded keep-node">
                <input type="checkbox" id="root" checked="checked"/>
                <label for="root" class="undecorated">
                    <?= htmlReady(Config::get()->UNI_NAME_CLEAN) ?>
                </label>
                <ul>
                <? $pos_id = 1; ?>
                <? foreach ((array) $tree as $node) : ?>
                    <? $children = $node->getChildren() ?>
                    <? if (count($children)) : ?>
                    <?= $this->render_partial('lvgroups/_node',
                        ['node' => $node, 'pos_id' => $pos_id++,
                            'open_nodes' => $open_lvg_nodes ?: [],
                            'search_result' => $search_result ?: [],
                            'children' => $node->getChildren()]) ?>
                    <? endif ?>
                <? endforeach; ?>
                </ul>
            </li>
        </ul>
    </div>
    <? if ($values['open_lvg_nodes']) : ?>
    <input type="hidden" name="open_nodes" value="<?= json_encode($values['open_lvg_nodes']) ?>"/>
    <? endif; ?>
    <? if ($values['searchterm']) : ?>
    <input type="hidden" name="searchterm" value="<?= $values['searchterm'] ?>"/>
    <? endif; ?>
    <script>
    $(function() {
        var element = $('#lvgroup-tree-search');
        element.on('keypress', function(e) {
            if (e.keyCode == 13) {
                if (element.val() != '') {
                    return STUDIP.MVV.CourseWizard.searchTree();
                } else {
                    return STUDIP.MVV.CourseWizard.resetSearch();
                }
            }
        });
    });

    </script>
<? endif; ?>
