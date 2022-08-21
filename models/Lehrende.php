<?php

class Lehrende extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'veranstalter';
        $config['has_one']['veranstaltung'] = [
            'class_name' => 'Veranstaltungsanmelden',
            'foreign_key' => 'id',
            'assoc_foreign_key' => 'id'
        ];
        $config['belongs_to']['lehrende'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_foreign_key' => 'user_id'
        ];
        parent::configure($config);
    }

public static function findByLecturer($id, $lecturer_id)
{
return self::findOneBySQL("id = ? AND user_id = ?", [$id, $lecturer_id]);
}

    public static function findByCourse($course_id)
    {
        $query = "SELECT veranstalter.*, aum.Vorname, aum.Nachname, aum.Email,
                         aum.username, ui.title_front, ui.title_rear
                         FROM veranstalter
                         LEFT JOIN auth_user_md5 aum USING (user_id)
                         LEFT JOIN user_info ui USING (user_id)
                         WHERE id = ?
                         ORDER BY  Nachname, Vorname";
        return DBManager::get()->fetchAll(
            $query,
            [$course_id],
            __CLASS__ . '::buildExisting'
        );
    }


}
?>


