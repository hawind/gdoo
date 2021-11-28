<?php namespace App\Illuminate\Database\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;
use Schema;
use Auth;
use DB;

use App\Support\License;

class Builder extends BaseBuilder
{
    /**
     * Insert a new record into the database.
     *
     * @param  array  $values
     * @return bool
     */
    public function insert(array $values)
    {
        // 判断演示模式
        License::demoCheck();

        $values = $this->checkColumnsValues($values);

        // 空数据
        if (empty($values)) {
            return 0;
        }

        return parent::insert($values);
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param  array   $values
     * @param  string  $sequence
     * @return int
     */
    public function insertGetId(array $values, $sequence = null)
    {
        // 判断演示模式
        License::demoCheck();

        $values = $this->checkColumnsValues($values);
        // 空数据
        if (empty($values)) {
            return 0;
        }

        return parent::insertGetId($values, $sequence);
    }

    /**
     * Update a record in the database.
     *
     * @param  array  $values
     * @return int
     */
    public function update(array $values)
    {
        // 判断演示模式
        License::demoCheck();

        $values = $this->checkColumnsValues($values, false);

        // 空数据
        if (empty($values)) {
            return 0;
        }

        return parent::update($values);
    }

    /**
     * 从数据库中删除记录。
     *
     * @param  mixed  $id
     * @return int
     */
    public function delete($id = null)
    {
        // 判断演示模式
        License::demoCheck();

        return parent::delete($id);
    }

    public function setValue($values, $columns, $insert)
    {
        if (is_array(current($values))) {
            foreach ($values as $k => $v) {
                // 递归处理多行数据
                $values[$k] = $this->setValue($v, $columns, $insert);
            }
        } else {
            // 删除不存在的字段的值
            $numbers = [
                'bigint' => 0,
                'int' => 0,
                'integer' => 0,
                'smallint' => 0,
                'tinyint' => 0,
                'decimal' => 0,
                'numeric' => 0,
            ];
            $dates = [
                'date' => null,
                'datetime' => null,
            ];

            $data = [];
            foreach($columns as $k => $column) {
                if ($column['is_identity'] == 1) {
                    continue;
                }

                if (array_key_exists($k, $values)) {
                    $v = $values[$k];
                    // 数字类型格式化
                    if (isset($numbers[$column['data_type']])) {
                        $v = floatval($v);
                    } else {
                        if ($v == '') {
                            if ($column['is_nullable'] == 0) {
                                if(isset($dates[$column['data_type']])) {
                                    $v = $dates[$column['data_type']];
                                } else {
                                    $v = null;
                                }
                            } else {
                                $v = null;
                            }
                        }
                    }
                    $data[$k] = $v;
                }
            }

            // 设置操作相关数据
            $data = $this->setAutoData($columns, $data, $insert);
        }

        return $data;
    }

    public function setAutoData(array $columns, array $data, $insert = true)
    {
        $user = auth()->user();

        $user_id = (int)$user['id'];
        $user_name = $user['name'];
        $time = time();
        $dt = date('Y-m-d H:i:s');

        $created_id = 'created_id';
        $created_by = 'created_by';
        $created_at = 'created_at';
        $created_dt = 'created_dt';

        $updated_id = 'updated_id';
        $updated_by = 'updated_by';
        $updated_at = 'updated_at';
        $updated_dt = 'updated_dt';

        if ($insert) {
            if (isset($columns[$created_id])) {
                $data[$created_id] = isset($data[$created_id]) ? $data[$created_id] : $user_id;
            }
            if (isset($columns[$created_by])) {
                $data[$created_by] = isset($data[$created_by]) ? $data[$created_by] : $user_name;
            }
            if (isset($columns[$created_at])) {
                $data[$created_at] = isset($data[$created_at]) ? $data[$created_at] : $time;
            }
            if (isset($columns[$created_dt])) {
                $data[$created_dt] = isset($data[$created_dt]) ? $data[$created_dt] : $dt;
            }
        } else {
            if (isset($columns[$updated_id])) {
                $data[$updated_id] = isset($data[$updated_id]) ? $data[$updated_id] : $user_id;
            }
            if (isset($columns[$updated_by])) {
                $data[$updated_by] = isset($data[$updated_by]) ? $data[$updated_by] : $user_name;
            }
            if (isset($columns[$updated_at])) {
                $data[$updated_at] = isset($data[$updated_at]) ? $data[$updated_at] : $time;
            }
            if (isset($columns[$updated_dt])) {
                $data[$updated_dt] = isset($data[$updated_dt]) ? $data[$updated_dt] : $dt;
            }
        }

        return $data;
    }

    public function checkColumnsValues(array $values, $insert = true)
    {
        $db_type = env('DB_CONNECTION');

        if ($db_type == 'sqlsrv') {
            $rows = DB::select("
            select column_name, 
            CASE WHEN is_nullable = 'YES' THEN 1 ELSE 0 END AS is_nullable,
            columnproperty(object_id(?), column_name, 'isIdentity') as is_identity, 
            data_type
            from information_schema.columns 
            where table_name=?", [$this->from, $this->from]);

        } else if($db_type == 'mysql') {
            $rows = DB::select("
            select column_name as column_name,
            CASE WHEN is_nullable = 'YES' THEN 1 ELSE 0 END AS is_nullable,
            CASE WHEN extra = 'auto_increment' THEN 1 ELSE 0 END AS is_identity,
            data_type as data_type
            from information_schema.columns 
            where table_schema=? and table_name=?", [DB::getDatabaseName(), $this->from]);
        }

        $columns = [];
        foreach($rows as $row) {
            $columns[$row['column_name']] = $row;
        }

        $values = $this->setValue($values, $columns, $insert);
        return $values;
    }
}
