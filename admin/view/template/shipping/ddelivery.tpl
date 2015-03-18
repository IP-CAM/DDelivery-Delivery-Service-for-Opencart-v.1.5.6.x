<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/shipping.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <h3>Уважаемые пользователи! Мы постарались сделать настройки наиболее гибкими, но от вас требуется внимательность при выборе параметров. Если Вам непонятно значение каких-то настроек, просим связатся с менеджерами DDelivery. В случае, если Вам потребуется больше настроек, так же просим связатся с клиентским отделом.</h3>  
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <!-- Группа основных настроек -->
          <tr>
          <td colspan="4"><h3><?php echo $main_settings; ?></h3></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_api; ?></td>
            <td><input type="text" name="ddelivery_api" size="40" maxlength="40" value="<?php echo $ddelivery_api; ?>" />
              <?php if ($error_api) { ?>
              <span class="error"><?php echo $error_api; ?></span>
              <?php } ?></td>
            <td colspan="2"><span class="help"><?php echo $entry_api_help; ?></span></td>
          </tr>
          <tr>
            <td><?php echo $entry_work_mode; ?></td>
            <td><select name="ddelivery_work_mode">
                <?php if ($ddelivery_work_mode == 'test') { ?>
                <option value="work"><?php echo $text_workmode_work; ?></option>
                <option value="test" selected="selected"><?php echo $text_workmode_test; ?></option>
                <?php } else { ?>
                <option value="work" selected="selected"><?php echo $text_workmode_work; ?></option>
                <option value="test"><?php echo $text_workmode_test; ?></option>
                <?php } ?>
              </select></td>
            <td colspan="2"><span class="help"><?php echo $entry_work_mode_help; ?></span></td>
          </tr>
          <tr>
            <td><?php echo $entry_cur_payment; ?></td>
            <td><select name="ddelivery_cur_payment">
                <?php foreach ($payment_methods as $payment_method) { ?>
                <?php if ($payment_method['id'] == $ddelivery_cur_payment) { ?>
                <option value="<?php echo $payment_method['id']; ?>" selected="selected"><?php echo $payment_method['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $payment_method['id']; ?>"><?php echo $payment_method['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            <td colspan="2"><span class="help"><?php echo $entry_cur_payment_help; ?></span></td>  
          </tr>
          <tr>
            <td><?php echo $entry_pvz_payment; ?></td>
            <td><select name="ddelivery_pvz_payment">
                <?php foreach ($payment_methods as $payment_method) { ?>
                <?php if ($payment_method['id'] == $ddelivery_pvz_payment) { ?>
                <option value="<?php echo $payment_method['id']; ?>" selected="selected"><?php echo $payment_method['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $payment_method['id']; ?>"><?php echo $payment_method['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            <td colspan="2"><span class="help"><?php echo $entry_pvz_payment_help; ?></span></td>  
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_insur; ?></td>
            <td><input type="text" name="ddelivery_insur" size="40" maxlength="40" value="<?php echo $ddelivery_insur; ?>" />
              <?php if ($error_insur) { ?>
              <span class="error"><?php echo $error_insur; ?></span>
              <?php } ?></td>
            <td colspan="2"><span class="help"><?php echo $entry_insur_help; ?></span></td>
          </tr>
          <tr>
            <td><?php echo $entry_theme; ?></td>
            <td><select name="ddelivery_theme">
                <?php foreach ($ddelivery_themes as $theme) { ?>
                <?php if ($theme == $ddelivery_theme) { ?>
                <option value="<?php echo $theme; ?>" selected="selected"><?php echo $theme; ?></option>
                <?php } else { ?>
                <option value="<?php echo $theme; ?>"><?php echo $theme; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            <td colspan="2"><span class="help"><?php echo $entry_theme_help; ?></span></td>
          </tr>
          <tr>
            <td><?php echo $entry_enabled_type; ?></td>
            <td><select name="ddelivery_enabled_type">
                <?php foreach ($ddelivery_enabled_types as $type) { ?>
                <?php if ($type['id'] == $ddelivery_enabled_type) { ?>
                <option value="<?php echo (int)$type['id']; ?>" selected="selected"><?php echo $type['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo (int)$type['id']; ?>"><?php echo $type['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            <td colspan="2"><span class="help"><?php echo $entry_enabled_type_help; ?></span></td>
          </tr>
          <tr>
            <td><?php echo $entry_show_contact_form ?></td>
            <td><select name="ddelivery_show_contact_form">
                <?php if ($ddelivery_show_contact_form) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select></td>
            <td colspan="2"><span class="help"><?php echo $entry_show_contact_form_help; ?></span></td>
          </tr>
          <tr>
            <td><?php echo $entry_display_time ?></td>
            <td><select name="ddelivery_display_time">
                <?php if ($ddelivery_display_time) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select></td>
            <td colspan="2"><span class="help"></span></td>  
          </tr>
          <tr>
            <td><?php echo $entry_length_class; ?></td>
            <td><select name="ddelivery_length_class_id">
                <?php foreach ($length_classes as $length_class) { ?>
                <?php if ($length_class['length_class_id'] == $ddelivery_length_class_id) { ?>
                <option value="<?php echo $length_class['length_class_id']; ?>" selected="selected"><?php echo $length_class['title']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $length_class['length_class_id']; ?>"><?php echo $length_class['title']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            <td colspan="2"><span class="help"></span></td>  
          </tr>
          <tr>
            <td><?php echo $entry_weight_class; ?></td>
            <td><select name="ddelivery_weight_class_id">
                <?php foreach ($weight_classes as $weight_class) { ?>
                <?php if ($weight_class['weight_class_id'] == $ddelivery_weight_class_id) { ?>
                <option value="<?php echo $weight_class['weight_class_id']; ?>" selected="selected"><?php echo $weight_class['title']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $weight_class['weight_class_id']; ?>"><?php echo $weight_class['title']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            <td colspan="2"><span class="help"></span></td>  
          </tr>          
          <tr>
            <td><?php echo $entry_tax_class; ?></td>
            <td><select name="ddelivery_tax_class_id">
                <option value="0"><?php echo $text_none; ?></option>
                <?php foreach ($tax_classes as $tax_class) { ?>
                <?php if ($tax_class['tax_class_id'] == $ddelivery_tax_class_id) { ?>
                <option value="<?php echo $tax_class['tax_class_id']; ?>" selected="selected"><?php echo $tax_class['title']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $tax_class['tax_class_id']; ?>"><?php echo $tax_class['title']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            <td colspan="2"><span class="help"></span></td>  
          </tr>
          <tr>
            <td><?php echo $entry_geo_zone; ?></td>
            <td><select name="ddelivery_geo_zone_id">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $ddelivery_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            <td colspan="2"><span class="help"></span></td>  
          </tr>
          <tr>
            <td><?php echo $entry_status ?></td>
            <td><select name="ddelivery_status">
                <?php if ($ddelivery_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select></td>
            <td colspan="2"><span class="help"></span></td>  
          </tr>
          <tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td><input type="text" name="ddelivery_sort_order" value="<?php echo $ddelivery_sort_order; ?>" size="1" /></td>
            <td colspan="2"><span class="help"></span></td>
          </tr>
          
          <!-- Группа настроек статусов -->
          <?php if (count($dd_statuses)): ?>
          <tr>
          <td colspan="4"><h3><?php echo $statuses_settings; ?></h3><span class="help"><?php echo $statuses_settings_desc; ?></span></td>
          </tr>
          <?php foreach ($dd_statuses as $status): ?>
          <?php
            $status_entry = 'entry_status_'.$status;
            $status_entry_help = 'entry_status_'.$status.'_help';
            $status_value = 'ddelivery_status_'.$status;
          ?>
          <tr>
            <td><?php echo $$status_entry; ?></td>
            <td><select name="ddelivery_status_<?php echo $status; ?>">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $$status_value) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            <td colspan="2"><span class="help"><?php echo $$status_entry_help; ?></span></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
          
          
          <!-- Группа настроек габаритов по умолчанию -->
          <tr>
          <td colspan="4"><h3><?php echo $size_settings; ?></h3><span class="help"><?php echo $size_settings_desc; ?></span></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_width; ?></td>
            <td><input type="text" name="ddelivery_width" size="40" maxlength="40" value="<?php echo $ddelivery_width; ?>" />
              <?php if ($error_width) { ?>
              <span class="error"><?php echo $error_width; ?></span>
              <?php } ?></td>
            <td colspan="2"><span class="help"><?php echo $entry_width_help; ?></span></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_length; ?></td>
            <td><input type="text" name="ddelivery_length" size="40" maxlength="40" value="<?php echo $ddelivery_length; ?>" />
              <?php if ($error_length) { ?>
              <span class="error"><?php echo $error_length; ?></span>
              <?php } ?></td>
            <td colspan="2"><span class="help"><?php echo $entry_length_help; ?></span></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_height; ?></td>
            <td><input type="text" name="ddelivery_height" size="40" maxlength="40" value="<?php echo $ddelivery_height; ?>" />
              <?php if ($error_height) { ?>
              <span class="error"><?php echo $error_height; ?></span>
              <?php } ?></td>
            <td colspan="2"><span class="help"><?php echo $entry_height_help; ?></span></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_weight; ?></td>
            <td><input type="text" name="ddelivery_weight" size="40" maxlength="40" value="<?php echo $ddelivery_weight; ?>" />
              <?php if ($error_weight) { ?>
              <span class="error"><?php echo $error_weight; ?></span>
              <?php } ?></td>
            <td colspan="2"><span class="help"><?php echo $entry_weight_help; ?></span></td>
          </tr>
          
          <!-- Группа настроек цены доставки -->
          <tr>
          <td colspan="4"><h3><?php echo $price_settings; ?></h3><span class="help"><?php echo $price_settings_desc; ?></span></td>
          </tr>
          <tr>
          <td><strong><?php echo $entry_price_from; ?></strong></td>
          <td><strong><?php echo $entry_price_to; ?></strong></td>
          <td><strong><?php echo $entry_pay_type; ?></strong></td>
          <td><strong><?php echo $entry_summ; ?></strong></td>
          </tr>
          <?php for($i=1; $i<=3; $i++){ ?>
          <tr>
          <td width="25%"><input type="text" name="ddelivery_price_from[]" value="<?php echo $ddelivery_price_from[$i-1]; ?>"</td>
          <td width="25%"><input type="text" name="ddelivery_price_to[]" value="<?php echo $ddelivery_price_to[$i-1]; ?>"</td>
          <td width="25%">
            <select name="ddelivery_pay_type[]" style="width:200px;">
                <?php foreach ($ddelivery_pay_types as $pay_type) { ?>
                <?php if ($pay_type['id'] == $ddelivery_pay_type[$i-1]) { ?>
                <option value="<?php echo (int)$pay_type['id']; ?>" selected="selected"><?php echo $pay_type['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo (int)$pay_type['id']; ?>"><?php echo $pay_type['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </td>
          <td width="25%"><input type="text" name="ddelivery_summ[]" value="<?php echo $ddelivery_summ[$i-1]; ?>"</td>
          </tr>
          <?php } ?>
          <tr>
            <td><?php echo $entry_zabor; ?><br /><span class="help"><?php echo $entry_zabor_help; ?></span></td>
            <td><input type="checkbox" name="ddelivery_zabor" value="1" <?php echo ((int)$ddelivery_zabor == 1)?'checked="checked"':'' ; ?> /></td>
            <td colspan="2"><span class="help"></span></td>
          </tr>
          <tr>
            <td><?php echo $entry_round; ?><br /><span class="help"><?php echo $entry_round_help; ?></span></td>
            <td><select name="ddelivery_round">
                <?php foreach ($ddelivery_round_types as $type) { ?>
                <?php if ($type['id'] == $ddelivery_round) { ?>
                <option value="<?php echo (int)$type['id']; ?>" selected="selected"><?php echo $type['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo (int)$type['id']; ?>"><?php echo $type['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            
            <td><span class="required">*</span> <?php echo $entry_round_step; ?><br /><span class="help"><?php echo $entry_round_step_help; ?></span></td>
            <td><input type="text" name="ddelivery_round_step" value="<?php echo (double)$ddelivery_round_step; ?>" />
                <?php if ($error_round_step) { ?>
              <span class="error"><?php echo $error_round_step; ?></span>
              <?php } ?></td>
          </tr>
          
          <!-- Группа настроек способов доставки -->
          <tr>
          <td colspan="4"><h3><?php echo $shipping_settings; ?></h3><span class="help"><?php echo $shipping_settings_desc; ?></span></td>
          </tr>
          <tr>
          <td colspan="2"><strong><?php echo $entry_cur_companies; ?></strong> (Выбрать все <input type="checkbox" id="ddelivery_cur_companies" />)</td>
          <td colspan="2"><strong><?php echo $entry_pvz_companies; ?></strong> (Выбрать все <input type="checkbox" id="ddelivery_pvz_companies" />)</td>
          </tr>
          <?php foreach($tks as $id => $tk){ ?>
          <tr>
          <td width="25%"><label for="ddelivery_cur_companies_<?php echo $id; ?>"><?php echo $tk; ?></label></td>
          <td width="25%"><input type="checkbox" id="ddelivery_cur_companies_<?php echo $id; ?>" name="ddelivery_cur_companies[]" value="<?php echo $id; ?>" <?php if (is_array($ddelivery_cur_companies) && array_search($id, $ddelivery_cur_companies)!==false) echo 'checked="checked"'; ?> class="ddelivery_cur_companies" /></td>
          <td width="25%"><label for="ddelivery_pvz_companies_<?php echo $id; ?>"><?php echo $tk; ?></label></td>
          <td width="25%"><input type="checkbox" id="ddelivery_pvz_companies_<?php echo $id; ?>" name="ddelivery_pvz_companies[]" value="<?php echo $id; ?>" <?php if (is_array($ddelivery_pvz_companies) && array_search($id, $ddelivery_pvz_companies)!==false) echo 'checked="checked"'; ?>  class="ddelivery_pvz_companies" /></td>
          </tr>
          <?php } ?>
          
        </table>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript">
<!--
	$(document).ready(function(){
	   $('.box').on('click','#ddelivery_cur_companies,#ddelivery_pvz_companies',function(){
	       $('.'+$(this).attr('id')).attr('checked',$(this).is(':checked'));
	   });
	});
-->
</script>
<?php echo $footer; ?>