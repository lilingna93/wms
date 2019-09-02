HI Venus team@2018 


#上线步骤
   更新线上机器配置
主仓wms：
cp -R /home/wms/app/Application /home/wms/backup/Application/Application.当前日期
cd /home/wms/app/Application/
rm -rf *
svn up
cp /home/wms/conf/config.php  /home/wms/app/Application/Common/Conf/config.php

软链更新静态页面
ln -s /home/wms/static/dist/index.html /home/wms/app/Application/Manage/View/Index/index.html

   更新静态资源
 cd  /home/wms/static/
 cp -R /home/wms/static/ /home/wms/backup/static/static.当前日期
 svn up 
 npm run build
 


#字典
   主仓：指为了科贸仓库独立部署的仓库系统，是Venus的系统主体
   副仓：指为了项目组运行的功能简化的仓库系统，是Venus的系统精精简版


#说明
#####1.副仓在功能上与主仓的差异
    副仓权限范围只有：库存货品管理，报表数据管理，系统账户管理
    1.1库存货品管理
    1.1.1库存货品管理-创建入仓单，免仓内操作默认是选中，且不可更改
    1.1.2库存货品管理-创建出仓单，添加货品，数量部分，SKU数量不可编辑，SPU数量可编辑
    1.1.3库存货品管理-创建出仓单，免仓内操作默认是选中，且不可更改
    
    1.2报表数据管理
    1.2.1客户单位选项默认选中当前登录账户所属仓库，不可编辑
    
    1.3系统账户管理
    1.3.1系统账户管理-仓库账户管理，添加账户中，权限部分只有，创建入仓单  入仓单管理  创建出仓单  出仓单管理  库存管理  报表管理  仓库账户管理，可编辑。其余隐藏
 
 
#脚本更新
>报表脚本

- 主仓wms：
    - vi /home/wms/app/Application/Common/Script/start_wms_report.php
    - define('APP_DIR', '/home/dev/venus/');前添加//
    - //define('APP_DIR', '/home/wms/app/');去除//
- 副仓iwms：
    - vi /home/iwms/app/Application/Common/Script/start_iwms_report.php
    - define('APP_DIR', '/home/dev/venus/');前添加//
    - //define('APP_DIR', '/home/wms/app/');去除//
>库存检测

- 主仓wms：
    - vi /home/wms/app/Application/Common/Script/check_wms_goods.php
    - define('APP_DIR', '/home/dev/venus/');前添加//
    - define('APP_DIR', '/home/wms/app/');去除//
- 副仓iwms：
    - vi /home/iwms/app/Application/Common/Script/check_iwms_goods.php
    - define('APP_DIR', '/home/dev/venus/');前添加//
    - define('APP_DIR', '/home/wms/app/');去除//



######备忘
- 外部订单的完成操作的时候，主仓sku规格中是n倍的spu。副仓sku规格中只是1倍的spu，所以在外部订单完成的时候，也就是主仓系统rpc调用副仓，做入仓操作的时候，货品的数量应该会根据上述差异做一个适配
- 理论上有了sku之后，所有的发起入仓和出仓的数据，都是先有spu数量，然后到写入表前计算出对应的sku数量。但对于入仓的时候，由于需要spu的采购价格，所以界面上需要及时算出spu价格



######退货业务相关的临时记录

1.表结构，请果芳根据设计稿，玲娜小仓2个RPC接口，大仓1个主要接口的处理逻辑做相应调整
CREATE TABLE IF NOT EXISTS `wms_ordergoodsreturn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ogr_code` varchar(16) NOT NULL   COMMENT '退货申请单编码',
  `ogr_type` tinyint(4) NOT NULL   COMMENT '退货申请单的退货类型，1:退数据，2:退货品',
  `ogr_status` tinyint(4) NOT NULL   COMMENT '退货申请单的退货类型，1:申请中，2:已处理，3:已拒绝',
  `goods_code` varchar(16) NOT NULL  COMMENT '退货相关的采购单内的货品编码',
  `goods_count` float(6,2) NOT NULL DEFAULT '0.00' COMMENT '退货相关的退货数量',
  `sku_code` varchar(16) NOT NULL COMMENT '退货货品的销售商品SKU编码',
  `sku_count` float(6,2) NOT NULL DEFAULT '0.00' COMMENT '退货货品的销售商品SKU数量',
  `spu_code` varchar(16) NOT NULL COMMENT '退货货品的销售商品SPU编码',
  `spu_count` int(11) NOT NULL COMMENT '退货货品的销售商品SKU内所含的SPU的倍数',
  `spu_sprice` float(6,2) NOT NULL DEFAULT '0.00' COMMENT '该商品下采购单时的销售价格',
  `spu_bprice` float(6,2) NOT NULL DEFAULT '0.00' COMMENT '该商品下采购单时的成本价格',
  `pro_percent` float(2,2) NOT NULL DEFAULT '0.00' COMMENT '可忽略',
  `profit_price` float(6,2) NOT NULL DEFAULT '0.00' COMMENT '该货品销售单位的利润金额',
  `order_code` varchar(16) NOT NULL COMMENT '退货相关的采购单编码',
  `ot_code` varchar(16) NOT NULL DEFAULT '' COMMENT '退货相关的采购单所属的分单任务编码',
  `supplier_code` varchar(16) NOT NULL COMMENT '该商品下采购单时的供应商编码',
  `user_code` varchar(16) NOT NULL,
  `war_code` varchar(16) NOT NULL COMMENT 'USER账户中TOKEN在小仓对应账户的仓库编号',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ogr_code` (`ogr_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

2.相应的Dao层请果芳根据上述确定后的表结构完成设计与开发
3.相应的Servive层，请在ReturnService.class.php开发后续接口以return作为标示
4.果芳作为该项目后端负责人，请大家配合