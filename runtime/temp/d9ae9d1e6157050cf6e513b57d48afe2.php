<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:101:"D:\phpStudy\PHPTutorial\WWW\jyzj.junyiqiche.com\public/../application/admin\view\sales\order\add.html";i:1555063397;s:90:"D:\phpStudy\PHPTutorial\WWW\jyzj.junyiqiche.com\application\admin\view\layout\default.html";i:1553824689;s:87:"D:\phpStudy\PHPTutorial\WWW\jyzj.junyiqiche.com\application\admin\view\common\meta.html";i:1555062052;s:89:"D:\phpStudy\PHPTutorial\WWW\jyzj.junyiqiche.com\application\admin\view\common\script.html";i:1553824689;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>
    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !$config['fastadmin']['multiplenav']): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <form id="add-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Customer_source'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
                        
            <select  id="c-customer_source" data-rule="required" class="form-control selectpicker" name="row[customer_source]">
                <?php if(is_array($customerSourceList) || $customerSourceList instanceof \think\Collection || $customerSourceList instanceof \think\Paginator): if( count($customerSourceList)==0 ) : echo "" ;else: foreach($customerSourceList as $key=>$vo): ?>
                    <option value="<?php echo $key; ?>" <?php if(in_array(($key), explode(',',"direct_the_guest"))): ?>selected<?php endif; ?>><?php echo $vo; ?></option>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </select>

        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Financial_name'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-financial_name" class="form-control" name="row[financial_name]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Username'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-username" data-rule="required" class="form-control" name="row[username]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Phone'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-phone" data-rule="required" class="form-control" name="row[phone]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Id_card'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-id_card" data-rule="required" class="form-control" name="row[id_card]" type="text" value="">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Genderdata'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            
            <div class="radio">
            <?php if(is_array($genderdataList) || $genderdataList instanceof \think\Collection || $genderdataList instanceof \think\Paginator): if( count($genderdataList)==0 ) : echo "" ;else: foreach($genderdataList as $key=>$vo): ?>
            <label for="row[genderdata]-<?php echo $key; ?>"><input id="row[genderdata]-<?php echo $key; ?>" name="row[genderdata]" type="radio" value="<?php echo $key; ?>" <?php if(in_array(($key), explode(',',"male"))): ?>checked<?php endif; ?> /> <?php echo $vo; ?></label> 
            <?php endforeach; endif; else: echo "" ;endif; ?>
            </div>

        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('City'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <div class='control-relative'><input id="c-city" data-rule="required" class="form-control" data-toggle="city-picker" name="row[city]" type="text" value=""></div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Models_name'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-models_name" data-rule="required" class="form-control" name="row[models_name]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Payment'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-payment" class="form-control" step="0.01" name="row[payment]" type="number">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Monthly'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-monthly" class="form-control" step="0.01" name="row[monthly]" type="number">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Nperlist'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
                        
            <select  id="c-nperlist" class="form-control selectpicker" name="row[nperlist]">
                <?php if(is_array($nperlistList) || $nperlistList instanceof \think\Collection || $nperlistList instanceof \think\Paginator): if( count($nperlistList)==0 ) : echo "" ;else: foreach($nperlistList as $key=>$vo): ?>
                    <option value="<?php echo $key; ?>" <?php if(in_array(($key), explode(',',"36"))): ?>selected<?php endif; ?>><?php echo $vo; ?></option>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </select>

        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Gps'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-gps" class="form-control" step="0.01" name="row[gps]" type="number" value="0.00">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Decoration'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-decoration" class="form-control" name="row[decoration]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Rent'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-rent" class="form-control" step="0.01" name="row[rent]" type="number">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Deposit'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-deposit" class="form-control" step="0.01" name="row[deposit]" type="number">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Family_members'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-family_members" class="form-control" name="row[family_members]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Detailed_address'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-detailed_address" class="form-control" name="row[detailed_address]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Family_members2'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-family_members2" class="form-control" name="row[family_members2]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Turn_to_introduce_name'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-turn_to_introduce_name" class="form-control" name="row[turn_to_introduce_name]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Turn_to_introduce_phone'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-turn_to_introduce_phone" class="form-control" name="row[turn_to_introduce_phone]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Turn_to_introduce_card'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-turn_to_introduce_card" class="form-control" name="row[turn_to_introduce_card]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Delivery_datetime'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-delivery_datetime" class="form-control datetimepicker" data-date-format="YYYY-MM-DD HH:mm:ss" data-use-current="true" name="row[delivery_datetime]" type="text" value="<?php echo date('Y-m-d H:i:s'); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Note_sales'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-note_sales" class="form-control" name="row[note_sales]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Type'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
                        
            <select  id="c-type" class="form-control selectpicker" name="row[type]">
                <?php if(is_array($typeList) || $typeList instanceof \think\Collection || $typeList instanceof \think\Paginator): if( count($typeList)==0 ) : echo "" ;else: foreach($typeList as $key=>$vo): ?>
                    <option value="<?php echo $key; ?>" <?php if(in_array(($key), explode(',',"mortgage"))): ?>selected<?php endif; ?>><?php echo $vo; ?></option>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </select>

        </div>
    </div>
    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-success btn-embossed disabled"><?php echo __('OK'); ?></button>
            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
        </div>
    </div>
</form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>