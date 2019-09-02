// 查询数据表
const QUERY_TABLE = (tableName) => `SELECT * FROM ${tableName}`

// 插入数据
const INSERT_TABLE = (tableName, {key, val}) => `INSERT INTO ${tableName} (${key}) VALUES (${val})`

// 更新数据
const UPDATE_TABLE = (tableName, {primaryKey, primaryVal}, {key, value}) => `UPDATE ${tableName} SET ${key}=${value} WHERE (${primaryKey}=${primaryVal});`

// 删除数据
const DELETE_TABLE = (tableName, {primaryKey, primaryVal}) => `DELETE ${tableName}  WHERE (${primaryKey}=${primaryVal});`

module.exports = {
    QUERY_TABLE,
    INSERT_TABLE,
    UPDATE_TABLE,
    DELETE_TABLE
}