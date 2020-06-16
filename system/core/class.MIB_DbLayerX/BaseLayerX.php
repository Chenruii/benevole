<?php


class BaseLayerX
{


    public function insertItem($table, $datas)
    {

        $fields = array_keys($datas);
        $values = array_values($datas);
        $fields = join(',', $fields);
        $values = "'" . join("','", $values) . "'";
        $sql = "INSERT INTO {$table} ($fields)  VALUES ($values)";
        return $this->query($sql);

    }

    public function updateItem($table, $datas, $id)
    {

        if (empty($id))
            return false;

        $updateParts = [];
        foreach ($datas as $k => $v) {
            $updateParts[] = " $k = '$v' ";
        }
        $updateParts = join(' , ', $updateParts);
        $sql = "UPDATE  {$table}  SET  $updateParts WHERE id = '$id'";
        return $this->query($sql);

    }

}