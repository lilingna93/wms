const {
    createLogger,
    format,
    transports
} = require('winston');
const {
    combine,
    timestamp,
    label,
    prettyPrint
} = format;
require('winston-daily-rotate-file')


var transport = new(transports.DailyRotateFile)({
    filename: './public/log/app-%DATE%.log',
    datePattern: 'YYYY-MM-DD',
    maxSize: '20m',
    maxFiles: '14d',
    format: combine(
        label({
            label: 'log end'
        }),
        timestamp(),
        prettyPrint()
    ),
});
transport.on('rotate', function (oldFilename, newFilename) {});

var logger = createLogger({
    transports: [
        transport
    ]
});


module.exports = {
    logger
}