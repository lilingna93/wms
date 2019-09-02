const schedule = require('node-schedule');
const task = require('./public/service/task');

const autoTask =  () => {
   schedule.scheduleJob('*/10 * * * *',()=>{
      task.task("ZTO");
   });
}

autoTask();