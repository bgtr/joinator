var response_json;

$(function(){
  $(document).on("click", ".btn_", function(){
    // 2問目以降
    var choice_id = $(this).data("value");
    $.ajax({
      type: "GET",
      url: "/cakephp/api/answer",
      data: {"karte_id":response_json.karte_id, "choice_id":choice_id}
    }).done(function(data) {
      // 描画
      draw(data);
      response_json = data;
    });
  });
});

/**
 * 初期化処理いろいろ
 */
function init(){
  $.ajax({
    type: "GET",
    url: "/cakephp/api/start",
    data: {"user":"hoge"}
  }).done(function(data){
    // 描画
    draw(data);
    response_json = data;
  });
}

/**
 * データをテンプレートに流し込む
 *
 */
function draw(data){
  $("#joi_img").attr("src",data.image);

  if(data.info.state == "question"){
    // 質問文
    $("#textarea").html(data.question.text);
    
    // 選択肢
    $.each(data.question.choices, function(i){
      $("#btn_"+i).attr("data-value",data.question.choices[i].value);
      $("#btn_caption_"+i).html(data.question.choices[i].text);
    });
  }else{
    // 結果発表
    $("#textarea").append(data.result);
  }
}

$(function(){
  init();
});
