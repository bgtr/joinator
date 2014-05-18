var response_json;

$(function(){
  init();
  $(document).on("click", ".btn_", function(){
    // 2問目以降
    var choice_id = $(this).data("value");

    $.ajax({
      type: "GET",
      url: "/cakephp/api/answer",
      data: {"karte_id":response_json.karte_id, "choice_id":choice_id, "index":response_json.index}
    }).done(function(data) {
      // 返答を返して会話してる感を出す
      $("#textarea").html(data.question.);
      
      // 描画
      window.setTimeout(function(){
        draw(data);
        response_json = data;
      }, 2000);
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

    // モーダルにグラフを仕込む
    var ctx = document.getElementById("myChart").getContext("2d");
    var myNewChart = new Chart(ctx).PolarArea(data);
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
      $("#btn_"+i).attr("data-value",data.question.choices[i].id)
                  .attr("data-reply",data.question.choices[i].reply);
      $("#btn_caption_"+i).html(data.question.choices[i].text)
                          .attr("data-value",data.question.choices[i].id)
                          .attr("data-reply",data.question.choices[i].reply);

    });
  }else{
    // 結果発表
    $("#textarea").append(data.result);
  }
}
