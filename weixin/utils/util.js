// const formatTime = date => {
//   const year = date.getFullYear()
//   const month = date.getMonth() + 1
//   const day = date.getDate()
//   const hour = date.getHours()
//   const minute = date.getMinutes()
//   const second = date.getSeconds()

//   return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
// }

const formatNumber = n => {
  n = n.toString()
  return n[1] ? n : '0' + n
}
const formatTime = date => {
  const nextDate = new Date(date.getTime());
  const year = nextDate.getFullYear()
  const month = nextDate.getMonth() + 1
  const day = nextDate.getDate()
  return [year, month, day].map(formatNumber).join('-')
}
const formatStartTime = date => {
  const nextDate = new Date(date.getTime() + 24 * 60 * 60 * 1000);
  const year = nextDate.getFullYear()
  const month = nextDate.getMonth() + 1
  const day = nextDate.getDate()
  return [year, month, day].map(formatNumber).join('-')
}
const formatEndTime = date => {
  const endDate = new Date(date.getTime() + 24 * 60 * 60 * 1000 * 7);
  var times = endDate.getFullYear() + "-" + (endDate.getMonth() + 1) + "-" + endDate.getDate();
  return times
}
module.exports = {
  formatStartTime: formatStartTime,
  formatEndTime: formatEndTime,
  formatTime: formatTime
}
