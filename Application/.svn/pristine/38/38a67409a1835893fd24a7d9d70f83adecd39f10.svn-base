const barcode = require('barcode');
const path = require('path');
const drawImg = require('./drawImage')
const log = require('../utils/winston');


let drawBarCode = (...arg) => {
    let [billCode, codeType, barcodeParams, codeMsg] = arg
    return new Promise((resolve, reject) => {
        try {
            code128 = barcode(codeType, barcodeParams);
            let outfile = path.join('./public/images/ZTO_barcode', `ZTO_barcode${barcodeParams.data}.jpg`)
            code128.saveImage(outfile, function (err) {
                if (err) {
                    log.logger.info({timestamp: new Date().toLocaleString(),msg:err || "生成barcode失败！"});
                    resolve({status: false, msg: '生成barcode失败', errMsg: err});
                    reject({errMsg: err})
                } else {
                    drawImg.drawImg(billCode, barcodeParams.data, codeMsg, resolve)
                }
            });
        }
        catch (err) {
            log.logger.info({timestamp: new Date().toLocaleString(),msg:err || "生成barcode失败！"});
            resolve({status: false, msg: 'catch,生成barcode失败'})
        }
    })
}


module.exports = {
    drawBarCode
}