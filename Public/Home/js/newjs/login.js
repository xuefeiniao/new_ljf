$(function () {
    /*输入框失去焦点时的效果*/
    $(".st-input").blur(function(){
        if($(this).val()==""){
            $(this).addClass("error").siblings("p.message").addClass("error").show();
        }else{
            $(this).removeClass("error").siblings("p.message").removeClass("error").hide();
        }
    });

    /*国家手机号选择*/
    $("input[autocomplete='off']").click(function(){
        if($(this).next("ul").css("display")=="none"){
            $(this).next("ul").show();
            $(this).find("span").addClass("active");
        }else{
            $(this).next("ul").hide();
            $(this).find("span").removeClass("active");
        }
    });
    $(".select-option").click(function(){
        var selectHtml = "+"+$(this).find("p").html().split("+")[1];
        $(this).closest("ul").siblings("p").html(selectHtml);
        $(this).closest("ul").hide();
        $(this).closest("ul").prev("p").find("span").removeClass("active");
    });
    // 点击空白区域弹框消失
    $(document).click(function(event){
        var _con = $('.st-select');   // 设置目标区域
        if(!_con.is(event.target) && _con.has(event.target).length === 0){ // Mark 1
            $('.st-select ul').hide().prev(".box").find("span").removeClass("active");          //消失
        }
    });

    /*用户协议勾选*/
    $(".checkboxSpan").click(function(){
        if($(this).hasClass("active")){
            $(this).removeClass("active");
            $(this).closest(".st-form-item").addClass("error");
            $(this).closest("label").siblings(".message").addClass("error").css("display","block");

        }else{
            $(this).addClass("active");
            $(this).closest(".st-form-item").removeClass("error");
            $(this).closest("label").siblings(".message").removeClass("error").css("display","none");
        }
        if($("span.active").length==2){
            $("#reg").addClass("active").attr("disable","false").css("cursor","pointer;");
        }else{
            $("#reg").removeClass("active").attr("disable","true").css("cursor","inherit");
        }
    });


    /*登录和注册切换*/
    $(".item").click(function(e){
        $(".item").find("span").css("display","none").prev("a").removeClass("active");
        $(this).find("span").css("display","block").prev("a").addClass("active");
        e.preventDefault();//取消冒泡
        if($(this).find("a").attr("href")=="/Login/register.html?login"){
            $(".loginDiv").show();
            $(".register").hide();

        }else{
            $(".loginDiv").hide();
            $(".register").show();

        }
    });



});


/*短信验证码倒计时*/
var countdown=60;
function settime(obj) {
    if (countdown == 0) {
        $(obj).attr("disabled","");
        $(obj).find("div").html("发送短信验证码");
        countdown = 60;
        return;
    } else {
        $(obj).attr("disabled", true);
        $(obj).find("div").html(+ countdown + "s");
        countdown--;
    }
    setTimeout(function() {
            settime(obj) }
        ,1000)
}