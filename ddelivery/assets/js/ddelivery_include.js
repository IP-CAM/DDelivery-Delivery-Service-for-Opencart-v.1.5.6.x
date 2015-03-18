var topWindow = parent;



while(topWindow != topWindow.parent) {
    topWindow = topWindow.parent;
}

$(document).ready(function(){
    
if(typeof(topWindow.DDeliveryIntegration) == 'undefined')
    topWindow.DDeliveryIntegration = (function(){
        var th = {};
        var status = 'Выберите условия доставки';
        var buttons = '#button-shipping-method,#simplecheckout_button_confirm,a#confirm,button#confirm,#button-confirm,#simplecheckout_next,#button-checkout' ;
        var button = null;
        
        th.getStatus = function(){
            return status;
        };
        
        
        function hideCover() {
            document.body.removeChild(document.getElementById('ddelivery_cover'));
        }

        function showPrompt() {
            var cover = document.createElement('div');
            cover.id = 'ddelivery_cover';
            document.body.appendChild(cover);
            document.getElementById('ddelivery_container').style.display = 'block';
        }
        
        function getFakeButton(){
            if ($('#fakeBtn').length > 0){
                $('#fakeBtn').remove();
            }
            if ($('#fakeBtn').length == 0){
                var text = '';
                $(buttons).each(function(idx){
                   if ($(this).is(':visible')){
                       if ($(this).val())
                        text = $(this).val(); 
                       else 
                        text = $(this).text();
                       button = $(this); 
                   }
                });
                var clone = button.clone();
                $(clone).attr('id','fakeBtn');
                $(clone).attr('onclick','');
                button.after(clone);
                clone.click(function(){
                    //alert('Сначала выберите точку доставки DDelivery');
                    DDeliveryIntegration.openPopup();
                });
            }
            return $('#fakeBtn');
            
        }
        
        th.showFakeButton = function showFakeButton(show){
            var fake_btn = getFakeButton();
            
            if (fake_btn == null && typeof fake_btn.css == 'undefined')
                return;
            
            if (show == true){
                $(button).css('display','none');
                $(fake_btn).css('display','inline-block');
            }
            else{
                $(button).css('display','inline-block');
                $(fake_btn).css('display','none'); 
                //alert((fake_btn.text())?fake_btn.text():fake_btn.val());   
            }
        }
        

        th.openPopup = function(){
            showPrompt();
            document.getElementById('ddelivery_popup').innerHTML = '';
            //jQuery('#ddelivery_popup').html('').modal().open();
            var params = {
                formData: {}
            };
            /*
            $($('#ORDER_FORM').serializeArray()).each(function(){
                params.formData[this.name] = this.value;
            });
            */

            var callback = {
                close: function(){
                    hideCover();
                    document.getElementById('ddelivery_container').style.display = 'none';
                    this.updatePage();
                    if ($('label#dd_info').text()=='') th.showFakeButton(true);
                    else th.showFakeButton(false);
                },
                change: function(data) {
                    status = data.comment;
                    
                    hideCover();
                    $('#ddelivery_container').css('display','none');
                    $('label#dd_info').text(data.comment);
                    $('label[for^=ddelivery]:eq(1)').text(data.clientPrice.toFixed(2) + ' руб.');
                    $('#button-shipping-method').css('display','inline-block');
                    $('input:radio[value*=ddelivery]').attr('checked',true);
                    this.updatePage();
                    th.showFakeButton(false);
                },
                updatePage: function(){
                    if (typeof overlay_simplecheckout !== 'undefined')
                        simplecheckout_reload('shipping_changed');
                    else if (typeof window.simplecheckout !== 'undefined' && typeof window.simplecheckout.reloadAll !== 'undefined')
                        window.simplecheckout.reloadAll();
                    else if (typeof simplecheckout_0 !== 'undefined' && typeof simplecheckout_0.reloadAll !== 'undefined'){
                        simplecheckout_0.reloadAll();
                    }
                    else if (typeof Simplecheckout !== 'undefined'){
                        var simplecheckout_0 = new Simplecheckout({
                        mainRoute: "checkout/simplecheckout",
                        additionalParams: "",
                        additionalPath: "",
                        mainUrl: "index.php?route=checkout/simplecheckout&group=0",
                        mainContainer: "#simplecheckout_form_0",
                        currentTheme: "univer",
                        loginBoxBefore: "",
                        displayProceedText: 1,
                        scrollToError: 1,
                        scrollToPaymentForm: 1,
                        useAutocomplete: 0,
                        useGoogleApi: 0,
                        popup: 0,
                        javascriptCallback: function() {}
                    });

                    simplecheckout_0.init();
                    simplecheckout_0.reloadAll();
                    }

                }
            };
            callback.updatePage();
            DDelivery.delivery('ddelivery_popup', 'ddelivery/ajax.php', {}, callback);
            return void(0);
        };
        var body = document.getElementsByTagName('div')[0];
        if ((body !== null) && (typeof body !== "undefined") ){
            
            var css = " #delivery_info_ddelivery_all a{display: none;} " +
                " #ddelivery_popup { display: inline-block; vertical-align: middle; margin: 10px auto; width: 1000px; height: 650px;} " +
                " #ddelivery_container { position: fixed; top: 0; left: 0; z-index: 9999;display: none; width: 100%; height: 100%; text-align: center;  } " +
                " #ddelivery_container:before { display: inline-block; height: 100%; content: ''; vertical-align: middle;} " +
                " #ddelivery_cover {  position: fixed; top: 0; left: 0; z-index: 2; width: 100%; height: 100%; background-color: #000; background: rgba(0, 0, 0, 0.5); filter: progid:DXImageTransform.Microsoft.gradient(startColorstr = #7F000000, endColorstr = #7F000000); } ";
                
            var head = document.head || document.getElementsByTagName('head')[0];
            var style = document.createElement('style');
            
            style.type = 'text/css';
            if (style.styleSheet){
              style.styleSheet.cssText = css;
            } else {
              style.appendChild(document.createTextNode(css));
            }
            head.appendChild(style);
            
            var div = document.createElement('div');
            div.innerHTML = '<div id="ddelivery_popup"></div>';
            div.id = 'ddelivery_container';
            body.appendChild(div);
            
        }

        return th;
    })();

var DDeliveryIntegration = topWindow.DDeliveryIntegration;

if (document.getElementById('select_way') == null){
    $('label[for^=ddelivery]:eq(0)').after('<a href="javascript:void(null)" onclick="DDeliveryIntegration.openPopup();" id="select_way" class="trigger">Выбрать точку доставки</a>');
}
    
$('input:radio[name=shipping_method]').click(function (){
    if ($(this).val() == 'ddelivery.ddelivery'){
        DDeliveryIntegration.openPopup();
        if ($('label#dd_info').text()==''){
            DDeliveryIntegration.showFakeButton(true);
            }
    }
    else{
        DDeliveryIntegration.showFakeButton(false);
    }
    
});


    setInterval(function(){
        if ($('input:radio[name=shipping_method]:checked').val() == 'ddelivery.ddelivery' && $('label#dd_info').text()=='' && 
            typeof $('#select_way') !== null && $('#select_way').is(':visible')){
            DDeliveryIntegration.showFakeButton(true);
        }
        else{
            //alert('hide');
            DDeliveryIntegration.showFakeButton(false);
            
        }    
    },1000);
});

/* Хуки на выбор компании или точки
mapPointChange: function(data) {},
courierChange: function(data) {}
*/