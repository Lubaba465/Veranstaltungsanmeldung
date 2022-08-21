<?php

require_once 'lib/admission.inc.php';
require_once 'lib/dates.inc.php';


class Veranstaltungsanmelden extends SimpleORMap
{
    /**
     * configure
     *
     * @param  mixed $config
     * @return void
     */

protected static function configure($config = [])
{


    $config['db_table'] = 'VAPlannung';
    $config['i18n_fields']['titel'] = true;
    $config['i18n_fields']['description'] = true;
    $config['i18n_fields']['vnummer'] = true;
    $config['i18n_fields']['sws'] = true;
    $config['i18n_fields']['angzugang'] = true;
    $config['i18n_fields']['ausstattung'] = true;
    $config['i18n_fields']['wunschraum'] = true;
    $config['has_many']['members'] = [
        'class_name' => 'Lehrende',
        'assoc_func' => 'findByCourse',
        'on_delete'  => 'delete',
        'on_store'   => 'store',
    ];
    $config['has_many']['modul'] = [
        'class_name' => 'Module',
        'assoc_func' => 'findByCourse',

        'on_delete'  => 'delete',
        'on_store'   => 'store',
    ];
    $config['has_many']['member'] = [
        'class_name' => 'Tutor',
        'assoc_func' => 'findByCourse',
        'on_delete'  => 'delete',
        'on_store'   => 'store',
    ];
    $config['has_many']['modul'] = [
        'class_name' => 'Module',
        'assoc_func' => 'findByCourse',
        'on_delete'  => 'delete',
        'on_store'   => 'store',
    ];
    $config['has_one']['home_institut'] = [
        'class_name' => 'Institute',
        'foreign_key' => 'institut_id',
        'assoc_foreign_key' => 'Institut_id'
    ]
    ;
    $config['has_one']['semesterdata'] = [
        'class_name' => 'Semester',
        'foreign_key' => 'semester_id',
        'assoc_foreign_key' => 'semester_id'
    ];
    $config['has_one']['semtypes'] = [
        'class_name' => 'SemType',
        'foreign_key' => 'sem_types_id',
        'assoc_foreign_key' => 'id'
    ];

    parent::configure($config);
}
/**
 * Find all entries.
 */
public static function findAll()
{
    return self::findBySQL('1 ORDER BY Titel');
}

public function getSemTypes()
{
    $types = [];
    foreach (SemType::getTypes() as $id => $type) {

        $types[$id] = $type;

    }
    return $types;
}
public function getType($id){

}

}

?>
