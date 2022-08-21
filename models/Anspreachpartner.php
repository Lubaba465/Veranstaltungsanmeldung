<?php


class Anspreachpartner extends SimpleORMap {
    static protected function configure($config = array()) {
        $config['db_table'] = 'anspreachpartner';
        $config['has_one']['veranstaltung'] = [
            'class_name' => 'Veranstaltungsanmelden',
            'foreign_key' => 'id',
            'assoc_foreign_key' => 'id'
        ];
        $config['belongs_to']['anspreachpartner'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_foreign_key' => 'user_id'
        ];
        parent::configure($config);
    }

    public static function findByAnspreachpartner($id, $anspreachpartner_id)
    {
        return self::findOneBySQL("id = ? AND user_id = ?", [$id, $anspreachpartner_id]);
    }}
?>
