<?php
namespace Manage\Controller;

use Common\Service\PassportService;
use Think\Controller;
use Wms\Dao\WarehouseDao;


class IndexErpController extends Controller {

    public function cli() {
    }

    public function index() {
        $workerData = PassportService::loginUser();
        if(empty($workerData)){
            $this->redirect("http://".C("WMS_HOST")."/manage/login/indexErp");
            return;
        }
        
        $config = array(
            "appname"=>("ERP系统"),
            "host"=>C("WMS_HOST"),
            "user" => array(
                "name" => $workerData["wor_name"],
                "code" => $workerData["wor_code"],
                "auth" => $workerData["wor_auth"],
                "rname" => $workerData["wor_rname"],
                "token" => $workerData["wor_token"],
                "phone" => $workerData["wor_phone"],
                "warcode" => $workerData["war_code"],
                "warname" => $workerData["war_name"],
                "houseType"=>!C("WMS_MASTER"),
            ),
            "warehouse" => $workerData["warehouses"],
            "receipt_type"=>$workerData["receipt_type"],
            "invoice_type"=>$workerData["invoice_type"],
            "type" => C("SPU_TYPE"),
            "subtype" => C("SPU_SUBTYPE"),
            "version" => C("VERSION"),
        );

        $this->assign('config', json_encode($config));
	$this->display();
    }
    public function indexErp() {
        $this->display();
    }	


    public function update(){
        $appversion = C("app_version");
        $this->assign('update_path', C("update_path")."/zwbee{$appversion}.air");
        $this->assign('app_version', $appversion);
        $this->assign('app_description', C("app_description"));
        $this->display();
    }
    
}
