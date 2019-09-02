const fetchMethods = require('../javascript/fetch');
const drawImg = require('../javascript/drawBarcode')
const api = require('../utils/api');
const strJion = require('../utils/strJion');
const param = require('../utils/params');
const log = require('../utils/winston');




const {query, insert} = require('../database/query')


let ZTO_service = async () => {
/*select * from wms_shoporders where logistics_status = 1*/

    let result = await query('select a.id,a.order_id, b.buyer_name, b.buyer_mobile, b.buyer_city, b.buyer_address, b.buyer_state, b.buyer_district \n' +
        'from wms_shopdismantleorder a left\n' +
        'join wms_shoporders b on a.tradenum = b.tradenum where a.logistics_status = 1', [])
    log.logger.info({message:'状态为1的数据',data_length:result.length+'条',data:result});

    for (let i = 0; i < result.length; i++) {
        const res = await task(i,result[i]);
        log.logger.info({message:'res',data:res});
    }
}
let address =  (str) => {
    return str.replace(/\s+/g, "");
}

let task = async (i,result) => {
    let sender_name = '曹金伟';
    let sender_mobile = '13811183973';
    let sender_province = '北京';
    let send_city = '北京市';
    let sender_city = '北京，北京市，房山区';
    let send_district = '房山区';
    let sender_address = '阎村科技园南门内东侧200米';
    let _mailAddress = '北京市房山区阎村科技园南门内东侧200米';

    //console.log(Math.random().toString().slice(-14));

    let buyer_address = address(result.buyer_address);



    let data = `{"partner":"e823306680284cf6a0beab0871862c65","id":"${result.order_id}","sender":{"name":"${sender_name}","mobile":"${sender_mobile}","city":"${sender_city}","address":"${sender_address}"},"receiver":{"name":"${result.buyer_name}","mobile":"${result.buyer_mobile}","city":"${result.buyer_city}","address":"${buyer_address}"}}`;


    let params = param.paramsHandle(data,'submitAgent');


    let res = await fetchMethods.globalFetch(api.InsertSubmitagent, params);

    let mData = `{"unionCode": "${result.order_id}","send_province": "${sender_province}","send_city": "${send_city}","send_district": "${send_district}","receive_province": "${result.buyer_state}","receive_city": "${result.buyer_city}","receive_district":"${result.buyer_district}","receive_address": "${buyer_address}"}`

    let markData = param.paramsHandle(mData,'GETMARK');
    let getmark = await fetchMethods.globalFetch(api.bagAddrMarkGetmark, markData);

    log.logger.info({timestamp: new Date().toLocaleString(),submitagent:res,bagAddrMark:getmark,msg:'fetch api'});

    if (!res.result || !getmark.status) {
        log.logger.info({timestamp: new Date().toLocaleString(),submitagent:res,bagAddrMark:getmark,msg:'api error'});
    } else {
        const codeType = 'code128';
        let billCode = res.data.billCode
        billCode = strJion.strJion(billCode);
        const barcodeParams = {data: res.data.billCode, width: 210, height: 40}
        let codeMsg = {
            type: "ZTO",
            order_id: result.order_id,
            _addressee: `${result.buyer_name}  ${result.buyer_mobile}`,
            _receivAddress:  buyer_address,
            _sender: `${sender_name} ${sender_mobile}`,
            _mailAddress: _mailAddress,
            id:result.id,
            getmark: getmark.result,
            message:result.buyer_message || ''
        }
        var status = await drawImg.drawBarCode(billCode, codeType, barcodeParams, codeMsg);
    }
    let results = {
        res: res,
        status: status || false
    }
    return results;
}


module.exports = {
    ZTO_service
}
