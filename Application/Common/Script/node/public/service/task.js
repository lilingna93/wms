let taskType = require('../utils/task_config')

let task = (type) => {
    taskType.taskType[type]();
}

module.exports = {
    task
}