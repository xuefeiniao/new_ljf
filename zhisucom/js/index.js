$(function(){
    // 历史工单记录标签切换
    var ticketTabs=$("#ticketTabs"),
        ticketsListDiv=$("#ticketsList"),
        fQAListDiv=$("#fQAList"),
        ticketItem=$("#ticketItem"),
        fQAItem=$("#fQAItem");
    if($.cookie("ticket_tabs") == 1) {
        fQAItem.addClass("ticket-active");
     $(".comment-list").hide();
        fQAListDiv.show();
        ticketItem.removeClass("ticket-active");
        ticketsListDiv.hide();
    } else {
        ticketItem.addClass("ticket-active");
      $(".comment-list").show();
        ticketsListDiv.show();
        fQAItem.removeClass("ticket-active");
        fQAListDiv.hide();
    }
    ticketTabs.on("click","li",function () {
        var _this=$(this);
        _this.addClass("ticket-active").siblings().removeClass("ticket-active");
        if(_this.attr("id") === 'ticketItem'){
            ticketsListDiv.show();
            fQAListDiv.hide();
            $(".comment-list").show();
            $.cookie("ticket_tabs",0);
        } else {
            fQAListDiv.show();
           $(".comment-list").hide();
            ticketsListDiv.hide();
            $.cookie("ticket_tabs",1);
        }
    });


    // 提交工单标签切换
    var contentTabs=$("#contentTabs"),
        currencyDiv=$(".ticket-currency"),
        depositDiv=$(".ticket-depo-txid"),
        withdrawDiv=$(".ticket-txid");
    if($.cookie("content_tabs") == 1) {
        $("#ticket_type").val('1');
        contentTabs.find("li").eq(0).removeClass("ticket-active");
        $("#withdrawItem").addClass("ticket-active");
        currencyDiv.show();
        depositDiv.hide();
        withdrawDiv.show();
    } else if($.cookie("content_tabs") == 2) {
        $("#ticket_type").val('2');
        contentTabs.find("li").eq(0).removeClass("ticket-active");
        $("#otherItem").addClass("ticket-active");
        currencyDiv.hide();
        withdrawDiv.hide();
        depositDiv.hide();
    } else {
        $("#ticket_type").val('0');
        contentTabs.find("li").eq(0).removeClass("ticket-active");
        $("#depositItem").addClass("ticket-active");
        currencyDiv.show();
        withdrawDiv.hide();
        depositDiv.show();
    }

    contentTabs.on("click","li",function () {
        var _this=$(this);
        _this.addClass("ticket-active").siblings().removeClass("ticket-active");
        if(_this.attr("id") == 'depositItem'){
            currencyDiv.show();
            withdrawDiv.hide();
            depositDiv.show();
            $.cookie("content_tabs",0);
            $("#ticket_type").val('0');
        } else if(_this.attr("id") == 'withdrawItem'){
            currencyDiv.show();
            depositDiv.hide();
            withdrawDiv.show();
            $.cookie("content_tabs",1);
            $("#ticket_type").val('1');
        } else {
            currencyDiv.hide();
            withdrawDiv.hide();
            depositDiv.hide();
            $.cookie("content_tabs",2);
            $("#ticket_type").val('2');
        }
        //withdrawtable.css("opacity","0").animate({opacity:"1"},200)
    });



    //工单提交
            var currencySlected = $("#currency option:selected").val();
            var currencySlected1;
            var currencySlected1Index = $("#ticket_type").val();
            var ticketTextSlected = $("#ticket_text").val();
            if(currencySlected1Index === 0){
                currencySlected1 = $('#depo_txid').vale();
            }
        if(currencySlected1Index === 1){
            currencySlected1 = $('#wd_oid').vale();
        }
    if(currencySlected1Index === 2){
        currencySlected1 = 0;
    }
    $('#submit').on('click', function(){
        $.ajax({
            url:'',
            type:'',
            data:{},
            success:function(res){
                console.log(res);
            },
            error:function(res){
                console.log(res);
            }
        })
    });





});