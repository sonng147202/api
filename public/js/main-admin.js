$.ajaxSetup({
  headers: {
    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
  },
});

$(".api-get-data-agency").change(function () {
  var code_agency = $(".api-get-data-agency").val();
  $.ajax({
    url: "/insurance/agency/get-agency",
    type: "POST",
    data: {
      code_agency,
    },
    success: function (result) {
      if (result.status == 1) {
          $(".level_agency_invite").val(result.level.code);
          $(".name_agency_invite").val(result.data.name);
          $(".email_agency_invite").val(result.user.email);
          $(".phone_agency_invite").val(result.data.phone);
      } else {
        $(".level_agency_invite").val();
        $(".name_agency_invite").val();
        $(".email_agency_invite").val();
        $(".phone_agency_invite").val();
      }

    },
  });
});

$(".api-get-data-agency2").change(function () {
  var code_agency = $(".api-get-data-agency2").val();
  $.ajax({
    url: "/insurance/agency/get-agency",
    type: "POST",
    data: {
      code_agency,
    },
    success: function (result) {
      if (result.status == 1) {
          $(".level_agency_parent").val(result.level.code);
          $(".name_agency_parent").val(result.data.name);
          $(".email_agency_parent").val(result.user.email);
          $(".phone_agency_parent").val(result.data.phone);
      } else {
        $(".level_agency_parent").val();
        $(".name_agency_parent").val();
        $(".email_agency_parent").val();
        $(".phone_agency_parent").val();
      }

    },
  });
});

function extendLevel(parentId) {
  var li = $("#li-" + parentId);
  console.log(li);
  console.log(li.hasClass("close-el"));
  if (li.hasClass("close-el")) {
      console.log('a');
      $.ajax({
      url: "/api/v1/agency/post-agency-child-list",
      type: "POST",
      data: {
        id: parentId
      },
      success: function (data) {
        if (data.status == 1) {
          console.log(data);
          $("#i-" + parentId).addClass('fa-minus-circle').removeClass('fa-plus-circle');
          $(li).addClass("open-el").removeClass("close-el");
          var list = data.data;
          if (list.length > 0) {
            var html = `<ul>`;
            var i;
            for (i = 0; i < list.length; i++){
              var check_update_info_agency = list[i].check_update_info_agency;
              var code_agency = list[i].code_agency;
              var level_code = list[i].level_code;
              var name = list[i].name;
              var id = list[i].id;
              var count_child = list[i].count_child;
              var html_i = "";
              var style = "";
              if (count_child > 0) {
                html_i = `<i class="fa fa-plus-circle extend-level" aria-hidden="true" data-id="`+ id +`" onclick="extendLevel(`+ id +`)" id="i-`+ id +`"></i>`;
              }

              if(check_update_info_agency == 0){
                style = 'style="color:red"';
              }

              html += `<li id="li-`+ id +`" class="close-el">
                        <span `+ style +`>`+ html_i + code_agency + `: ` + name + ` (` + level_code + `)</span>
                    </li>`;
            }
            html += `</ul>`;
            li.append(html);
          }
        }
      },
    });
  } else {
    console.log("b");
    $("#i-" + parentId).addClass('fa-plus-circle').removeClass('fa-minus-circle');
    $(li).addClass("close-el").removeClass("open-el");
    $("#li-" + parentId + " ul").remove();
  }
}
function extendInviteLevel(parentId) {
  var li = $("#li-" + parentId);
  console.log(li);
  console.log(li.hasClass("close-el"));
  if (li.hasClass("close-el")) {
      console.log('a');
      $.ajax({
      url: "/insurance/agency/get-agency-invite-child-list",
      type: "POST",
      data: {
        id: parentId
      },
      success: function (data) {
        if (data.status == 1) {
          console.log(data);
          $("#i-" + parentId).addClass('fa-minus-circle').removeClass('fa-plus-circle');
          $(li).addClass("open-el").removeClass("close-el");
          var list = data.data;
          if (list.length > 0) {
            var html = `<ul>`;
            var i;
            for (i = 0; i < list.length; i++){
              var check_update_info_agency = list[i].check_update_info_agency;
              var code_agency = list[i].code_agency;
              var level_code = list[i].level_code;
              var name = list[i].name;
              var id = list[i].id;
              var count_child = list[i].count_child;
              var html_i = "";
              var style = "";
              if (count_child > 0) {
                html_i = `<i class="fa fa-plus-circle extend-level" aria-hidden="true" data-id="`+ id +`" onclick="extendInviteLevel(`+ id +`)" id="i-`+ id +`"></i>`;
              }

              if(check_update_info_agency == 0){
                style = 'style="color:red"';
              }

              html += `<li id="li-`+ id +`" class="close-el">
                        <span `+ style +`>`+ html_i + code_agency + `: ` + name + ` (` + level_code + `)</span>
                    </li>`;
            }
            html += `</ul>`;
            li.append(html);
          }
        }
      },
    });
  } else {
    console.log("b");
    $("#i-" + parentId).addClass('fa-plus-circle').removeClass('fa-minus-circle');
    $(li).addClass("close-el").removeClass("open-el");
    $("#li-" + parentId + " ul").remove();
  }
}
$(document).ready(function () {
  $('.levels-multiple').select2();
  $('.revenue-cycle-multiple').select2();
  $('.offices-multiple').select2();
  var readImageSingleURL = function (input) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $(".profile-pic").attr("src", e.target.result);
      };
      reader.readAsDataURL(input.files[0]);
    }
  };

  $(".file-single-upload").on("change", function () {
    readImageSingleURL(this);
  });

  $(".profile-pic").on("click", function () {
    $(".file-single-upload").click();
  });

    $(".icon-add-image").click(function () {
        var type = $(this).data("type");    
        $(".upload-" + type).trigger("click");
    });

    $(".uploadFile").change(function (event) {
        var type = $(this).data("type");    
        var user = $(this).data("user");    
        imagesPreview(this, ".list-image-" + type, type, user);
  });

  var imagesPreview = function (input, placeToInsertImagePreview, type, user) {
    if (input.files) {
      var count = input.files.length;
      if (count > 0) {
        var formData = new FormData();
        formData.append("type", type);
        formData.append("user", user);
        for (i = 0; i < count; i++) {
          formData.append("fileArr[]", input.files[i]);
        }
        $.ajax({
          url: "/insurance/agency/update-image-post",
          type: "POST",
          data: formData,
          contentType: false,
          cache: false,
          processData: false,
            success: function (data) {
                if (data.status == 1) {
                    var count = data.image.length;
                    if (count > 0) {
                        for (i = 0; i < count; i++){
                            var img = data.image[i];
                            var html =`<div class="image-item">
                                            <img src="` + img +`" width="100%">
                                            <span class="icon-remove" data-url="`+ img +`" data-type="`+type+`"><i class="fa fa-times-circle-o" aria-hidden="true"></i></span>
                                        </div>`;
                            $(placeToInsertImagePreview).append(html);
                        }
                        var arrOld = ($(placeToInsertImagePreview + " .json_img").val() != '') ? JSON.parse($(placeToInsertImagePreview + " .json_img").val()) : [];
                        var arrNew = (arrOld.length == 0) ? data.image : arrOld.concat(data.image);
                        $(placeToInsertImagePreview + " .json_img").val(JSON.stringify(arrNew));
                    }
                }
          },
        });
      }
    }
    };
     $(".icon-remove").click(function () {
        var type = $(this).data("type");
        var url = $(this).data("url");
         console.log(type);
         console.log(url);
         var arrOld = ($(".list-image-" + type + " .json_img").val() != '') ? JSON.parse($(".list-image-" + type + " .json_img").val()) : [];
         if (arrOld.length > 0) {
            arrNew = $.grep(arrOld, function (value) {
              return value != url;
            });
             (arrNew.length > 0) ? $(".list-image-" + type + " .json_img").val(JSON.stringify(arrNew)) : $(".list-image-" + type + " .json_img").val('');
             $(this).parent().remove();
         }
     });
    function removeItem(array, item) {
      for (var i in array) {
        if (array[i] == item) {
          array.splice(i, 1);
          break;
        }
      }
  }
  $(".change-agency-level").change(function () {
    var val = $(".change-agency-level").val();
    if(val == 1 || val == 2){
      $('.button-change-agency-level').text('Lưu thông tin');
    }else if(val == 3 || val == 4){
      $('.button-change-agency-level').text('Gửi thư Offer gia nhập');
    }else {
      $('.button-change-agency-level').text('Gửi tới MEDICI xét duyệt');
    }
  });
  $(".amount_paid_format").change(function () {
    var money = $(".amount_paid_format").val();
    money = money.split(' ').join('');
    money = money.split('.').join('');
    money = money.split(',').join('');
    if(money != ''){
      money = parseInt(money);
      money = money.toLocaleString('it-IT', {style : 'currency', currency : 'VND'});
      money = money.replace([" VND"], ['']);
      $(".amount_paid_format").val(money)
    }else {
      $(".amount_paid_format").val(0)
    }
    
  });
  $(".net_amount_format").change(function () {
    var money = $(".net_amount_format").val();
    money = money.split(' ').join('');
    money = money.split('.').join('');
    money = money.split(',').join('');
    if(money != ''){
      money = parseInt(money);
      money = money.toLocaleString('it-IT', {style : 'currency', currency : 'VND'});
      money = money.replace([" VND"], ['']);
      $(".net_amount_format").val(money)
    }else {
      $(".net_amount_format").val(0)
    }
    
  });
  $(".gross_amount_format").change(function () {
    var money = $(".gross_amount_format").val();
    money = money.split(' ').join('');
    money = money.split('.').join('');
    money = money.split(',').join('');
    if(money != ''){
      money = parseInt(money);
      money = money.toLocaleString('it-IT', {style : 'currency', currency : 'VND'});
      money = money.replace([" VND"], ['']);
      $(".gross_amount_format").val(money)
    }else {
      $(".gross_amount_format").val(0)
    }
    
  });
  $(".total_amount_sup_format").change(function () {
    var money = $(".total_amount_sup_format").val();
    money = money.split(' ').join('');
    money = money.split('.').join('');
    money = money.split(',').join('');
    if(money != ''){
      money = parseInt(money);
      money = money.toLocaleString('it-IT', {style : 'currency', currency : 'VND'});
      money = money.replace([" VND"], ['']);
      $(".total_amount_sup_format").val(money)
    }else {
      $(".total_amount_sup_format").val(0)
    }
  });


  window.onbeforeunload = function(){
    // Show loading
    $('.content-box .overlay').removeClass('hide');
  };
  $('#revenue_type').change(function(){
    var revenue_type = $('#revenue_type').val();
    $("#revenue_year").hide();
    $("#revenue_quarter").hide();
    $("#revenue_month").hide();
    if(revenue_type == 1){
      $("#revenue_year").show();
    }else if(revenue_type == 2){
      $("#revenue_year").show();
      $("#revenue_quarter").show();
    }else if(revenue_type == 3){
      $("#revenue_year").show();
      $("#revenue_month").show();
    }
  });

  $("#form_email_revenue_from").change(function () {
    var money = $("#form_email_revenue_from").val();
    if(money != ''){
      money = money.replace(".", "");
      money = parseInt(money);
      money = money.toLocaleString('it-IT', {style : 'currency', currency : 'VND'});
      money = money.replace([" VND"], ['']);
      $("#form_email_revenue_from").val(money)
    }else {
      $("#form_email_revenue_from").val(0)
    }
  });

  $("#form_email_revenue_to").change(function () {
    var money = $("#form_email_revenue_to").val();
    if(money != ''){
      money = money.replace(".", "");
      money = parseInt(money);
      money = money.toLocaleString('it-IT', {style : 'currency', currency : 'VND'});
      money = money.replace([" VND"], ['']);
      $("#form_email_revenue_to").val(money)
    }else {
      $("#form_email_revenue_to").val(0)
    }
  });

  $(".checkSendAll").on("click", function(){
    let string = '';
    $('.form-search').serializeArray().forEach(element => {
        string += element.name + '=' + element.value + '&';
    });
    
    if($(this).is(":checked")){
        string += 'isCheckAll=true';
        $(".checkSend").prop("checked", true);
    }else{
        string += 'isCheckAll=false';
        $(".checkSend").prop("checked", false);
    }
    $.ajax({
        url : '/insurance/notification/email-add?'+string,
        type : 'get',
        data : {},
        success : function (result){
          $('#users-list').val(JSON.stringify(result));
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert('Something went wrong');
        }
    });
});

  $(".checkSend").click(function () {
    var user_id = $(this).val();
    $.ajax({
      url : "/insurance/notification/check-send",
      type : "post",
      data : {
        user_id,
      },
      success : function (result){
          $('#users-list').val(JSON.stringify(result));
      }
    });
  });

  $(".send-notification").click(function () {
    var users = $('#users-list').val();
    users = users.split('[').join('');
    users = users.split(']').join('');
    if(users.length > 0){
      var userList = users.split(",");
      $('.modal-content-send-mail .submit-send-mail').removeAttr('disable');
      $('.modal-content-send-mail .users').val(JSON.stringify(userList));
      $('.modal-content-send-mail').modal('show');
    }else {
      // console.log('121212');
      alert('Vui lòng chọn người nhận !')
    }
    
  });
  $(".submit-send-mail").click(function () {
    // var users = $('.modal-content-send-mail .users').val();
    // if(users.length > 0){
      
    // }
    var subject = $('.modal-content-send-mail .subject').val();
      var content = tinyMCE.activeEditor.getContent();
      $('.validate-subject').text('');
      $('.validate-content').text('');
      content = content.split('<div id="eJOY__extension_root" class="eJOY__extension_root_class" style="all: unset;">&nbsp;</div>').join('');
      
      if(subject.length == 0) {
        $('.validate-subject').text('Tiêu đề không được bỏ trống');
      }

      if(content.length == 0) {
        $('.validate-content').text('Nội dung không được bỏ trống');
      }

      if(subject.length > 0 && content.length > 0){
        $('.form-notification').submit();
      }
  });


  var readImageMultipleURL = function (input) {
    console.log(input.files);
    if(input.files.length > 0){
      var html = '';
      for(var i = 0 ; i < input.files.length; i++){
        var remove_file = "'"+input.files[i].name+"'";
        html += '<div class="file"><img src="/img/icon-file.png" width="100px"><p class="file-name">'+input.files[i].name+'</p><span class="remove-file" data-file-name="'+input.files[i].name+'" onclick="removeMultipleFile('+remove_file+')"><i class="fa fa-times" aria-hidden="true"></i></span></div>';
      }
      $('.list-file').append(html);
    }
  };

  $(".attach_file").on("change", function () {
    $('.attach_file_delete').val('');
    $('.list-file').html('');
    readImageMultipleURL(this);
  });

  $(".icon-upload-multiple").on("click", function () {
    $(".attach_file").click();
  });

});

function removeMultipleFile(file_name){
  console.log('file_name' + file_name);
  var file_remove = $("[data-file-name='"+file_name+"']");
  var attach_file_delete = $('.attach_file_delete').val();
  attach_file_delete = attach_file_delete.split('[').join('');
  attach_file_delete = attach_file_delete.split(']').join('');
  attach_file_delete = attach_file_delete.split('"').join('');
  if(attach_file_delete.length > 0){
    var file = attach_file_delete.split(",");
  }else {
    var file = [];
  }
  console.log('file' + file);
  file.push(file_name); 
  $('.attach_file_delete').val(JSON.stringify(file));
  console.log('json' + JSON.stringify(file));
  file_remove.parent().remove()
}
// $('.datepicker-full').datepicker({
//   format: 'dd/mm/yyyy',
//   todayHighlight: true
//   // endDate: new Date()
// });

