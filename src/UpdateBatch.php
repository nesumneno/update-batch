<?php

namespace Mavinoo\UpdateBatch;

use Illuminate\Support\Facades\DB;

class UpdateBatch
{
    /**
     * Update Multi fields
     * $table String
     * $value Array
     * $index String
     *
     * Example
     *
     * $table = 'users';
     * $value = [
     *      [
     *          'id' => 1,
     *          'status' => 'active',
     *          'nickname' => 'Mohammad'
     *      ] ,
     *      [
     *          'id' => 5,
     *          'status' => 'deactive',
     *          'nickname' => 'Ghanbari'
     *      ] ,
     * ];
     *
     * $index = 'id';
     *
     */

    public function updateBatch($table, $values, $index)
    {
        $final  = array();
        $ids    = array();

        if(!count($values))
            return false;
        if(!isset($index) AND empty($index))
            return 'Select Key for Update';

        foreach ($values as $key => $val)
        {
            $ids[] = $val[$index];
            foreach (array_keys($val) as $field)
            {
                if ($field == 'i18n_first') continue;
                if ($field !== $index)
                {
                    if (is_array($val[$field])) {
                        $val[$field] = addslashes(json_encode($val[$field], JSON_UNESCAPED_UNICODE));
                    } else {
                        $val[$field] = htmlspecialchars($val[$field], ENT_QUOTES, 'UTF-8');
                    }
                    $final[$field][] = 'WHEN `'. $index .'` = "' . $val[$index] . '" THEN "' . $val[$field] . '" ';
                }
            }
        }

        $cases = '';
        foreach ($final as $k => $v)
        {
            $cases .= $k.' = (CASE '. implode("\n", $v) . "\n"
                            . 'ELSE '.$k.' END), ';
        }

        $query = 'UPDATE ' . $table . ' SET '. substr($cases, 0, -2) . ' WHERE ' . $index . ' IN('.implode(',', $ids).')';

        return DB::statement($query);
    }
}
