<?php


require_once 'lib/admission.inc.php';
require_once 'lib/dates.inc.php';

class Module extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'vermod';
        $config['has_one']['veranstaltung'] = [
            'class_name' => 'Veranstaltungsanmelden',
            'foreign_key' => 'id',
            'assoc_foreign_key' => 'id'
        ];
        $config['belongs_to']['Module'] = [
            'class_name' => 'Modul',

        ];
        parent::configure($config);
    }

    public static function findByModul($id, $modul_ID)
    {
        return self::findOneBySQL("id = ? AND modul_ID = ?", [$id, $modul_ID]);
    }

    public static function findAllByModuleAndStudiengang($id)
    {
        return self::findBySQL("id = ?", [$id]);
    }


        public static function storeNewAndDelete($thesis_id, $values){

        $existing_data = Module::findBySQL('id = ? ', [$thesis_id]);


        foreach ($existing_data as $singe_data) {
            if (!$values[$singe_data->modul_id]) {
                $singe_data->delete();
            }
        }

        // Neue Werte hinzufügen
        foreach ($values as $modul_id => $assigned) {
            $new_contact = Module::findBySQL(
                'id = ? AND modul_id = ?',
                [$thesis_id, $modul_id]
            );
            if (!$new_contact) {
                $new_contact = new Module();
                $new_contact->id = $thesis_id;
                $new_contact->modul_id = $modul_id;
                $new_contact->store();
            }
        }


    }
    public static function findByCourse($course_id)
    {
        $query = "SELECT vermod.*
                         FROM vermod
 LEFT JOIN mvv_modul_deskriptor mmd USING(modul_id)
             LEFT JOIN     mvv_modul mm     USING(modul_id)
                         WHERE id = ?
                        ";
        /*"SELECT modul_id, CONCAT(mmd.bezeichnung, ' (', code, ')') as name FROM mvv_modul mm
                    LEFT JOIN mvv_modul_deskriptor mmd USING(modul_id)
                    WHERE (code LIKE :input OR mmd.bezeichnung LIKE :input)
    ORDER BY name ASC*/
        return DBManager::get()->fetchAll(
            $query,
            [$course_id],
            __CLASS__ . '::buildExisting'
        );
    }


}
?>
