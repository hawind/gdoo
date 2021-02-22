<?php

/**
 * 把时间戳转换成时间
 */
function sql_timestamp_datetime($value) {
    $db_type = env('DB_CONNECTION');
    if ($db_type == 'sqlsrv') {
        $value = "DATEADD(ss, {$value}, '1970-01-01 08:00:00')";
    } elseif ($db_type == 'mysql') {
        $value = "from_unixtime({$value})";
    }
    return $value;
}

/**
 * 获取sql年
 */
function sql_year($value, $type = 'date') {
    
    $db_type = env('DB_CONNECTION');
    $sql = null;
    if ($type == 'ts') {
        $value = sql_timestamp_datetime($value);
    }
    if ($db_type == 'sqlsrv') {
        $sql = "year({$value})"; 
    } else if($db_type == 'mysql') {
        $sql = "year({$value})"; 
    }
    return $sql;
}

/**
 * 获取sql月
 */
function sql_month($value, $type = 'date') {
    
    $db_type = env('DB_CONNECTION');
    $sql = null;
    if ($type == 'ts') {
        $value = sql_timestamp_datetime($value);
    }
    if($db_type == 'sqlsrv') {
        $sql = "month({$value})"; 
    } else if($db_type == 'mysql') {
        $sql = "month({$value})"; 
    }
    return $sql;
}

/**
 * (时间戳,时间)字段转换为年月
 */
function sql_year_month($value, $type = 'date') {
    $db_type = env('DB_CONNECTION');
    $sql = null;
    if ($type == 'ts') {
        $value = sql_timestamp_datetime($value);
    }
    if($db_type == 'sqlsrv') {
        $sql = "CONVERT(varchar(7), {$value}, 120)";
    } else if($db_type == 'mysql') {
        $sql = "DATE_FORMAT({$value}, '%Y-%m')";
    }
    return $sql;
}

/**
 * (时间戳,时间)字段转换为年周
 */
function sql_year_week($value, $type = 'date') {
    $db_type = env('DB_CONNECTION');
    $sql = null;
    if ($type == 'ts') {
        $value = sql_timestamp_datetime($value);
    }
    if ($db_type == 'sqlsrv') {
        $sql = "DATEPART(week, {$value})";
    } else if($db_type == 'mysql') {
        $sql = "WEEK({$value}, 1)";
    }
    return $sql;
}


/**
 * (时间戳,时间)字段转换为年月日
 */
function sql_year_month_day($value, $type = 'date') {
    $db_type = env('DB_CONNECTION');
    $sql = null;
    if ($type == 'ts') {
        $value = sql_timestamp_datetime($value);
    }
    if ($db_type == 'sqlsrv') {
        $sql = "CONVERT(varchar(10), {$value}, 120)";
    } else if($db_type == 'mysql') {
        $sql = "DATE_FORMAT({$value}, '%Y-%m-%d')";
    }
    return $sql;
}

/**
 * (时间戳, 时间)字段转换为月日
 */
function sql_month_day($value, $type = 'date') {
    $db_type = env('DB_CONNECTION');
    $sql = null;
    if ($type == 'ts') {
        $value = sql_timestamp_datetime($value);
    }
    if($db_type == 'sqlsrv') {
        $sql = "substring(convert(varchar(10), {$value}, 120), 6, 10)";
    } else if($db_type == 'mysql') {
        $sql = "DATE_FORMAT({$value}, '%m-%d')";
    }
    return $sql;
}

/**
 * 浮点类型转换成字符串(主要正对sqlserver)
 */
function sql_float_varchar($value, $length = 255) {
    $db_type = env('DB_CONNECTION');
    $sql = null;
    if($db_type == 'sqlsrv') {
        $sql = "CONVERT(VARCHAR({$length}), {$value})";
    } else if($db_type == 'mysql') {
        $sql = $value;
    }
    return $sql;
}

/**
 * 实现不同的日期对比
 */
function sql_is_date($value) {
    $db_type = env('DB_CONNECTION');
    $sql = null;
    if ($db_type == 'sqlsrv') {
        $sql = $value; 
    } else if($db_type == 'mysql') {
        $sql = $value; 
    }
    return $sql;
}

/**
 * 实现不同的天差
 */
function sql_day_diff($field, $value) {
    $db_type = env('DB_CONNECTION');
    $sql = null;
    if($db_type == 'sqlsrv') {
        $sql = "datediff(day, $field, '$value')";
    } else if($db_type == 'mysql') {
        $sql = "datediff($field, '$value')";
    }
    return $sql;
}